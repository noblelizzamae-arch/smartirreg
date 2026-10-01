<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SensorReading;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Default admin user ──────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@smartirreg.com'],
            [
                'name'     => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        // ── Sensor readings (last 14 days, every 2 hours) ──
        SensorReading::truncate();

        $now = Carbon::now();

        for ($day = 13; $day >= 0; $day--) {
            for ($hour = 0; $hour < 24; $hour += 2) {
                $time = $now->copy()->subDays($day)->setHour($hour)->setMinute(0)->setSecond(0);

                // Simulate realistic fluctuations
                $temp  = round(20 + (sin(($hour / 24) * M_PI * 2) * 6) + (rand(-10, 10) / 10), 2);
                $humid = round(60 + (cos(($hour / 24) * M_PI * 2) * 10) + (rand(-15, 15) / 10), 2);
                $soil  = round(40 + (sin(($day  / 14) * M_PI * 2) * 8) + (rand(-10, 10) / 10), 2);

                SensorReading::create([
                    'temperature'  => $temp,
                    'humidity'     => $humid,
                    'soil_moisture' => $soil,
                    'recorded_at'  => $time,
                ]);
            }
        }

        $this->command->info('Seeded 1 admin user and ' . (14 * 12) . ' sensor readings.');
    }
}
