<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SensorController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ── Blynk / ESP32 Sensor Endpoints ───────────────────────────────────────
Route::post('/sensor/store',      [SensorController::class, 'store']);
Route::get('/sensor/latest',      [SensorController::class, 'latest']);
Route::get('/sensor/blynk-live',  [SensorController::class, 'blynkLive']);
Route::get('/sensor/blynk-pull',  [SensorController::class, 'blynkPull']);

// ── Debug endpoint (remove in production) ────────────────────────────────
Route::get('/sensor/debug', function () {
    $token  = env('BLYNK_AUTH_TOKEN');
    $server = env('BLYNK_SERVER', 'blynk.cloud');

    $pins = ['V0','V1','V2','V3','V4'];
    $results = [];

    foreach ($pins as $pin) {
        $r = \Illuminate\Support\Facades\Http::timeout(10)
            ->withoutVerifying()
            ->get("https://{$server}/external/api/get", [
                'token' => $token,
                'pin'   => $pin,
            ]);
        $results[$pin] = [
            'status' => $r->status(),
            'body'   => $r->body(),
        ];
    }

    $online = \Illuminate\Support\Facades\Http::timeout(10)
        ->withoutVerifying()
        ->get("https://{$server}/external/api/isHardwareConnected", ['token' => $token]);

    return response()->json([
        'token'   => substr($token, 0, 8) . '...',
        'server'  => $server,
        'online'  => $online->body(),
        'pins'    => $results,
    ]);
});
