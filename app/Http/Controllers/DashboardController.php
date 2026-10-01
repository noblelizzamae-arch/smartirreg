<?php

namespace App\Http\Controllers;

use App\Models\SensorReading;
use App\Services\BlynkService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // ── Auto-pull latest data from Blynk Cloud ─────────────────
        try {
            $blynk = app(BlynkService::class);
            $blynk->pullAndSave();
        } catch (\Exception $e) {
            // Silent fail — don't crash dashboard if Blynk is unreachable
        }
        $mode     = $request->get('mode', 'daily');
        $search   = $request->get('search', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo   = $request->get('date_to', '');

        // ── Latest & previous readings (always needed for stat cards) ──
        $latest   = SensorReading::latest('id')->first();
        $previous = SensorReading::latest('id')->skip(1)->first();

        $tempChange  = $this->pctChange($previous?->temperature,   $latest?->temperature);
        $humidChange = $this->pctChange($previous?->humidity,      $latest?->humidity);
        $soilChange  = $this->pctChange($previous?->soil_moisture, $latest?->soil_moisture);

        // ── Device status & activity log (always needed) ──
        $recentReadings = SensorReading::latest('id')->take(50)->get();

        $deviceEvents = collect();
        foreach ($recentReadings as $reading) {
            $time = Carbon::parse($reading->recorded_at)->format('M d, Y h:i A');

            // Pump ON: soil moisture < 40% (matches ESP32 soilDryThreshold = 40)
            if ($reading->soil_moisture < 40) {
                $deviceEvents->push([
                    'device'  => 'Irrigation Pump',
                    'color'   => 'pump',
                    'message' => 'Pump turned ON to irrigate plant beds',
                    'detail'  => 'Soil moisture was ' . number_format($reading->soil_moisture, 1) . '% (below 40% threshold)',
                    'time'    => $time,
                ]);
            }
            // Mist ON: temp > 30°C OR humidity < 60% (matches ESP32 misting logic)
            if ($reading->temperature > 30 || $reading->humidity < 60) {
                $deviceEvents->push([
                    'device'  => 'Misting System',
                    'color'   => 'sprinkler',
                    'message' => 'Misting system turned ON',
                    'detail'  => 'Temp: ' . number_format($reading->temperature, 1) . '°C / Humidity: ' . number_format($reading->humidity, 1) . '%',
                    'time'    => $time,
                ]);
            }
        }
        $deviceEvents = $deviceEvents->take(15);

        // Device status — matches ESP32 thresholds exactly
        $pumpOn      = $latest && $latest->soil_moisture < 40;
        $sprinklerOn = $latest && ($latest->temperature > 30 || $latest->humidity < 60);
        $fanOn       = false; // No separate fan relay in this setup

        // ── Mode-based data ────────────────────────────────────────────
        $labels    = [];
        $tempData  = [];
        $humidData = [];
        $soilData  = [];

        // For summary table rows (weekly & monthly)
        $summaryRows = collect();

        if ($mode === 'daily') {
            // ── DAILY: every reading from today ──────────────────────
            $query = SensorReading::whereDate('recorded_at', Carbon::today());

            // Apply search/date filters on top
            if ($dateFrom) $query->where('recorded_at', '>=', Carbon::parse($dateFrom)->startOfDay());
            if ($dateTo)   $query->where('recorded_at', '<=', Carbon::parse($dateTo)->endOfDay());
            if ($search)   $query->where('recorded_at', 'like', '%' . $search . '%');

            $readings = $query->orderBy('recorded_at')->get();

            // No data today → fall back to the most recent day that has data
            if ($readings->isEmpty() && !$dateFrom && !$dateTo && !$search) {
                $latestDate = SensorReading::latest('id')->value('recorded_at');
                if ($latestDate) {
                    $readings = SensorReading::whereDate('recorded_at', Carbon::parse($latestDate)->toDateString())
                        ->orderBy('id')
                        ->get();
                }
            }

            $labels    = $readings->map(fn($r) => Carbon::parse($r->recorded_at)->format('H:i'))->toArray();
            $tempData  = $readings->pluck('temperature')->map(fn($v) => round($v, 1))->toArray();
            $humidData = $readings->pluck('humidity')->map(fn($v) => round($v, 1))->toArray();
            $soilData  = $readings->pluck('soil_moisture')->map(fn($v) => round($v, 1))->toArray();

            // Daily summary table: one row per hour with averages
            $summaryRows = $readings->groupBy(fn($r) => Carbon::parse($r->recorded_at)->format('H:00'))
                ->map(function ($group, $hour) {
                    return (object)[
                        'label'        => $hour,
                        'avg_temp'     => round($group->avg('temperature'), 2),
                        'max_temp'     => round($group->max('temperature'), 2),
                        'min_temp'     => round($group->min('temperature'), 2),
                        'avg_humidity' => round($group->avg('humidity'), 2),
                        'max_humidity' => round($group->max('humidity'), 2),
                        'min_humidity' => round($group->min('humidity'), 2),
                        'avg_soil'     => round($group->avg('soil_moisture'), 2),
                        'max_soil'     => round($group->max('soil_moisture'), 2),
                        'min_soil'     => round($group->min('soil_moisture'), 2),
                        'reading_count'=> $group->count(),
                    ];
                })->values();

        } elseif ($mode === 'weekly') {
            // ── WEEKLY: last 7 days grouped by day ───────────────────
            $query = SensorReading::where('recorded_at', '>=', Carbon::now()->subDays(6)->startOfDay());

            if ($dateFrom) $query->where('recorded_at', '>=', Carbon::parse($dateFrom)->startOfDay());
            if ($dateTo)   $query->where('recorded_at', '<=', Carbon::parse($dateTo)->endOfDay());
            if ($search)   $query->where('recorded_at', 'like', '%' . $search . '%');

            $readings = $query->orderBy('recorded_at')->get();

            // No data in last 7 days → fall back to the 7 most recent days that have data
            if ($readings->isEmpty() && !$dateFrom && !$dateTo && !$search) {
                $readings = SensorReading::orderBy('recorded_at', 'desc')
                    ->take(200)
                    ->get()
                    ->sortBy('recorded_at');
            }

            // Group by day
            $grouped = $readings->groupBy(fn($r) => Carbon::parse($r->recorded_at)->format('Y-m-d'));

            $labels    = $grouped->keys()->map(fn($d) => Carbon::parse($d)->format('D, M d'))->toArray();
            $tempData  = $grouped->map(fn($g) => round($g->avg('temperature'), 1))->values()->toArray();
            $humidData = $grouped->map(fn($g) => round($g->avg('humidity'), 1))->values()->toArray();
            $soilData  = $grouped->map(fn($g) => round($g->avg('soil_moisture'), 1))->values()->toArray();

            // Weekly summary table: one row per day
            $summaryRows = $grouped->map(function ($group, $date) {
                return (object)[
                    'label'        => Carbon::parse($date)->format('D, M d'),
                    'avg_temp'     => round($group->avg('temperature'), 2),
                    'max_temp'     => round($group->max('temperature'), 2),
                    'min_temp'     => round($group->min('temperature'), 2),
                    'avg_humidity' => round($group->avg('humidity'), 2),
                    'max_humidity' => round($group->max('humidity'), 2),
                    'min_humidity' => round($group->min('humidity'), 2),
                    'avg_soil'     => round($group->avg('soil_moisture'), 2),
                    'max_soil'     => round($group->max('soil_moisture'), 2),
                    'min_soil'     => round($group->min('soil_moisture'), 2),
                    'reading_count'=> $group->count(),
                ];
            })->values();

        } elseif ($mode === 'monthly') {
            // ── MONTHLY: grouped by week-of-month (Week 1–4 per month) ──
            $query = SensorReading::query();

            if ($dateFrom) $query->where('recorded_at', '>=', Carbon::parse($dateFrom)->startOfDay());
            if ($dateTo)   $query->where('recorded_at', '<=', Carbon::parse($dateTo)->endOfDay());
            if ($search)   $query->where('recorded_at', 'like', '%' . $search . '%');

            $allReadings = $query->orderBy('recorded_at')->get();

            // Fallback: if empty with no filters, get everything
            if ($allReadings->isEmpty() && !$dateFrom && !$dateTo && !$search) {
                $allReadings = SensorReading::orderBy('recorded_at')->get();
            }

            // Group by week-of-month, label includes date range e.g. "Jul W1 (1-7)"
            $grouped = $allReadings->groupBy(function ($r) {
                $dt      = Carbon::parse($r->recorded_at);
                $week    = min(4, (int) ceil($dt->day / 7));
                $dayFrom = ($week - 1) * 7 + 1;
                $dayTo   = $week === 4
                    ? $dt->copy()->endOfMonth()->day   // last week ends on last day of month
                    : $week * 7;
                $label   = $dt->format('M Y') . ' W' . $week . ' (' . $dayFrom . '-' . $dayTo . ')';
                return $dt->format('Y-m') . '|W' . $week . '|' . $label;
            });

            // Sort by the key (YYYY-MM|WN is lexicographically correct)
            $grouped = $grouped->sortKeys();

            $labels    = $grouped->keys()->map(fn($k) => explode('|', $k)[2])->toArray();
            $tempData  = $grouped->map(fn($g) => round($g->avg('temperature'), 1))->values()->toArray();
            $humidData = $grouped->map(fn($g) => round($g->avg('humidity'), 1))->values()->toArray();
            $soilData  = $grouped->map(fn($g) => round($g->avg('soil_moisture'), 1))->values()->toArray();

            $summaryRows = $grouped->map(function ($group, $key) {
                $label = explode('|', $key)[2];
                return (object)[
                    'label'        => $label,
                    'avg_temp'     => round($group->avg('temperature'), 2),
                    'max_temp'     => round($group->max('temperature'), 2),
                    'min_temp'     => round($group->min('temperature'), 2),
                    'avg_humidity' => round($group->avg('humidity'), 2),
                    'max_humidity' => round($group->max('humidity'), 2),
                    'min_humidity' => round($group->min('humidity'), 2),
                    'avg_soil'     => round($group->avg('soil_moisture'), 2),
                    'max_soil'     => round($group->max('soil_moisture'), 2),
                    'min_soil'     => round($group->min('soil_moisture'), 2),
                    'reading_count'=> $group->count(),
                ];
            })->values();
        }

        return view('dashboard', compact(
            'latest', 'previous',
            'tempChange', 'humidChange', 'soilChange',
            'labels', 'tempData', 'humidData', 'soilData',
            'summaryRows',
            'mode', 'search', 'dateFrom', 'dateTo',
            'deviceEvents', 'pumpOn', 'sprinklerOn', 'fanOn'
        ));
    }

    private function pctChange($old, $new): ?float
    {
        if (is_null($old) || is_null($new) || $old == 0) {
            return null;
        }
        return round((($new - $old) / $old) * 100, 2);
    }

    /**
     * Returns latest sensor data as JSON for auto-refresh.
     * GET /dashboard/live
     */
    public function live()
    {
        // Pull fresh data from Blynk
        try {
            app(BlynkService::class)->pullAndSave();
        } catch (\Exception $e) {}

        $latest   = SensorReading::latest('id')->first();
        $previous = SensorReading::latest('id')->skip(1)->first();

        if (!$latest) {
            return response()->json(['success' => false]);
        }

        return response()->json([
            'success'      => true,
            'temperature'  => number_format($latest->temperature,   1),
            'humidity'     => number_format($latest->humidity,      1),
            'soil'         => number_format($latest->soil_moisture, 1),
            'temp_change'  => $this->pctChange($previous?->temperature,   $latest->temperature),
            'humid_change' => $this->pctChange($previous?->humidity,      $latest->humidity),
            'soil_change'  => $this->pctChange($previous?->soil_moisture, $latest->soil_moisture),
            'pump_on'      => $latest->soil_moisture < 40,
            'mist_on'      => $latest->temperature > 30 || $latest->humidity < 60,
            'recorded_at'  => Carbon::parse($latest->recorded_at)->format('M d, Y h:i A'),
        ]);
    }
}
