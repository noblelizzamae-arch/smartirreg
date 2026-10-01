<?php

namespace App\Services;

use App\Models\SensorReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlynkService
{
    protected string $token;
    protected string $server;

    public function __construct()
    {
        $this->token  = config('blynk.auth_token') ?: env('BLYNK_AUTH_TOKEN');
        $this->server = config('blynk.server')     ?: env('BLYNK_SERVER', 'blynk.cloud');
        // Strip https:// if accidentally included
        $this->server = str_replace(['https://', 'http://'], '', $this->server);
    }

    /**
     * Read a single virtual pin from Blynk Cloud.
     * GET https://blynk.cloud/external/api/get?token=TOKEN&pin=V0
     */
    public function getPin(string $pin): ?string
    {
        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->get("https://{$this->server}/external/api/get", [
                    'token' => $this->token,
                    'pin'   => $pin,
                ]);

            if ($response->successful()) {
                // Blynk returns plain value e.g. 34.3 or ["34.3"]
                $body = trim($response->body());
                // Strip JSON array brackets if present
                $body = trim($body, '["\ ]');
                return $body !== '' ? $body : null;
            }

            Log::warning("[Blynk] getPin({$pin}) HTTP {$response->status()}: {$response->body()}");
            return null;

        } catch (\Exception $e) {
            Log::error("[Blynk] getPin({$pin}): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if ESP32 is online on Blynk Cloud.
     */
    public function isDeviceOnline(): bool
    {
        try {
            $response = Http::timeout(5)->get(
                "https://{$this->server}/external/api/isHardwareConnected",
                ['token' => $this->token]
            );
            // Blynk returns plain string "true" or "false"
            return $response->successful() && str_contains($response->body(), 'true');
        } catch (\Exception $e) {
            Log::error("[Blynk] isDeviceOnline: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Pull all virtual pins from Blynk Cloud.
     * V0=Temp, V1=Humidity, V2=Pump, V3=Mist, V4=SoilMoisture
     */
    public function fetchSensorData(): array
    {
        return [
            'temperature'   => (float) ($this->getPin('V0') ?? 0),
            'humidity'      => (float) ($this->getPin('V1') ?? 0),
            'pump'          => (int)   ($this->getPin('V2') ?? 0),
            'mist'          => (int)   ($this->getPin('V3') ?? 0),
            'soil_moisture' => (float) ($this->getPin('V4') ?? 0),
        ];
    }

    /**
     * Pull from Blynk Cloud and save to sensor_readings table.
     */
    public function pullAndSave(): ?SensorReading
    {
        $data = $this->fetchSensorData();

        Log::info('[Blynk] pullAndSave data: ' . json_encode($data));

        // Only skip if device is completely unreachable (all null from getPin)
        if ($data['temperature'] === 0.0 && $data['humidity'] === 0.0 && $data['soil_moisture'] === 0.0) {
            // Double-check device online before giving up
            if (!$this->isDeviceOnline()) {
                Log::warning('[Blynk] Device offline, skipping save.');
                return null;
            }
        }

        $reading = SensorReading::create([
            'temperature'   => round($data['temperature'],   2),
            'humidity'      => round($data['humidity'],      2),
            'soil_moisture' => round($data['soil_moisture'], 2),
            'recorded_at'   => Carbon::now(),
        ]);

        Log::info("[Blynk] Saved → Temp:{$reading->temperature} Humid:{$reading->humidity} Soil:{$reading->soil_moisture}");
        return $reading;
    }
}
