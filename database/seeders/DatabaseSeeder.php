<?php

namespace Database\Seeders;

use App\Monitoring\MonitoringAccess;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app(MonitoringAccess::class)->demo()) {
            $this->call(MonitoringDemoSeeder::class);
        }
    }
}
