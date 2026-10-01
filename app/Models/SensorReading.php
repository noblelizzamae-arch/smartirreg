<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'temperature',
        'humidity',
        'soil_moisture',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at'  => 'datetime',
        'temperature'  => 'float',
        'humidity'     => 'float',
        'soil_moisture' => 'float',
    ];
}
