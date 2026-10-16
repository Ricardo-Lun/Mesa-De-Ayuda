<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Device;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Device::create([
            'owner' => 'Ana López',
            'area' => 'Recursos Humanos',
            'brand' => 'Dell',
            'model' => 'Latitude 5440',
            'serial_number' => 'DL5440-001',
            'type' => 'Laptop',
            'received_at' => '2026-09-15',
            'priority' => 'high',
        ]);

        Device::create([
            'owner' => 'Jorge Ramírez',
            'area' => 'Contabilidad',
            'brand' => 'HP',
            'model' => 'ProDesk 600 G6',
            'serial_number' => 'HP600G6-002',
            'type' => 'Desktop',
            'received_at' => '2026-09-18',
            'priority' => 'medium',
        ]);

        Device::create([
            'owner' => 'Mariana Sánchez',
            'area' => 'Dirección',
            'brand' => 'Epson',
            'model' => 'EcoTank L3250',
            'serial_number' => 'EPSON-L3250-003',
            'type' => 'Printer',
            'received_at' => '2026-09-20',
            'priority' => 'low',
        ]);
    }
}