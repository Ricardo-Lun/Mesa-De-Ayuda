<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Device;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carlos = User::where('email', 'carlos.mendoza@example.com')->firstOrFail();
        $laura = User::where('email', 'laura.torres@example.com')->firstOrFail();

        $laptop = Device::where('serial_number', 'DL5440-001')->firstOrFail();
        $desktop = Device::where('serial_number', 'HP600G6-002')->firstOrFail();
        $printer = Device::where('serial_number', 'EPSON-L3250-003')->firstOrFail();

        Service::create([
            'folio' => 'ST-2026-001',
            'device_id' => $laptop->id,
            'technician_id' => $carlos->id,
            'status' => 'in_progress',
        ]);

        Service::create([
            'folio' => 'ST-2026-002',
            'device_id' => $desktop->id,
            'technician_id' => $laura->id,
            'status' => 'completed',
        ]);

        Service::create([
            'folio' => 'ST-2026-003',
            'device_id' => $printer->id,
            'technician_id' => $carlos->id,
            'status' => 'pending',
        ]);
    }
}