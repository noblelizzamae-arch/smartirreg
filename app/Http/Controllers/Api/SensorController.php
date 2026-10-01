<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorReading;
use App\Services\BlynkService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SensorController extends Controller
{
    protected BlynkService $blynk;

    public function __construct(BlynkService $blynk)
    {
        $this->blynk = $blynk;
    }

    // ── Auth helper ──────────────────────────────────────────────────────
    private function authorized(Request $request): bool
    {
        $key = $request->header('X-API-KEY') ?? $request->query('api_key');
        return $key === config('blynk.api_key');
    }

    /**
     * POST /api/sensor/store
     * ESP32 sends JSON directly to Laravel (optional path).
     * Header: X-API-KEY: smartirreg_secret_2026
     */
    public function store(Request $request)
    {
        if (!$this->authorized($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $validated = $request->validate([
            'temperature'   => 'required|numeric|between:-50,100',
            'humidity'      => 'required|numeric|between:0,100',
            'soil_moisture' => 'required|numeric|between:0,100',
        ]);

        $reading = SensorReading::create([
            'temperature'   => round($validated['temperature'],   2),
            'humidity'      => round($validated['humidity'],      2),
            'soil_moisture' => round($validated['soil_moisture'], 2),
            'recorded_at'   => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sensor reading saved.',
            'data'    => $reading,
            'devices' => [
                'pump' => $reading->soil_moisture < 40,
                'mist' => $reading->temperature > 30 || $reading->humidity < 60,
            ],
        ], 201);
    }

    /**
     * GET /api/sensor/latest?api_key=<key>
     * Returns latest reading from DB.
     */
    public function latest(Request $request)
    {
        if (!$this->authorized($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $reading = SensorReading::latest('recorded_at')->first();

        if (!$reading) {
            return response()->json(['success' => false, 'message' => 'No readings yet.'], 404);
        }

        return response()->json([
            'success'       => true,
            'device_online' => $this->blynk->isDeviceOnline(),
            'data'          => $reading,
            'devices'       => [
                'pump' => $reading->soil_moisture < 40,
                'mist' => $reading->temperature > 30 || $reading->humidity < 60,
            ],
        ]);
    }

    /**
     * GET /api/sensor/blynk-live?api_key=<key>
     * Returns live values directly from Blynk Cloud virtual pins.
     */
    public function blynkLive(Request $request)
    {
        if (!$this->authorized($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        return response()->json([
            'success'       => true,
            'device_online' => $this->blynk->isDeviceOnline(),
            'data'          => $this->blynk->fetchSensorData(),
        ]);
    }

    /**
     * GET /api/sensor/blynk-pull?api_key=<key>
     * Pulls live values from Blynk Cloud and saves to DB.
     */
    public function blynkPull(Request $request)
    {
        if (!$this->authorized($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $reading = $this->blynk->pullAndSave();

        if (!$reading) {
            return response()->json([
                'success' => false,
                'message' => 'Device offline or returned no data.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reading pulled from Blynk and saved to DB.',
            'data'    => $reading,
        ], 201);
    }
}
