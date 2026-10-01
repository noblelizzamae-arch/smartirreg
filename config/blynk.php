<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Blynk Cloud Credentials
    |--------------------------------------------------------------------------
    */

    'auth_token'  => env('BLYNK_AUTH_TOKEN'),
    'server'      => env('BLYNK_SERVER', 'blynk.cloud'),
    'template_id' => env('hpcSzkZatVN-rv6rB8iVBJsFci7NQ6t7'),

    /*
    |--------------------------------------------------------------------------
    | Sensor API Key (protects POST /api/sensor/store from ESP32)
    |--------------------------------------------------------------------------
    */

    'api_key' => env('SENSOR_API_KEY', 'changeme'),

];
