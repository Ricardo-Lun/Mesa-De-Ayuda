<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Activity;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carlos = User::where('email', 'carlos.mendoza@example.com')->firstOrFail();
        $laura = User::where('email', 'laura.torres@example.com')->firstOrFail();

        $serviceLaptop = Service::where('folio', 'ST-2026-001')->firstOrFail();
        $serviceDesktop = Service::where('folio', 'ST-2026-002')->firstOrFail();
        $servicePrinter = Service::where('folio', 'ST-2026-003')->firstOrFail();

        Activity::create([
            'service_id' => $serviceLaptop->id,
            'technician_id' => $carlos->id,
            'performed_at' => '2026-09-15',
            'description' => 'Se realizó diagnóstico inicial del equipo y se identificó un problema con el sistema operativo.',
        ]);

        Activity::create([
            'service_id' => $serviceLaptop->id,
            'technician_id' => $carlos->id,
            'performed_at' => '2026-09-16',
            'description' => 'Se repararon archivos del sistema y se instalaron actualizaciones pendientes.',
        ]);

        Activity::create([
            'service_id' => $serviceDesktop->id,
            'technician_id' => $laura->id,
            'performed_at' => '2026-09-18',
            'description' => 'Se realizó mantenimiento preventivo y limpieza interna del equipo.',
        ]);

        Activity::create([
            'service_id' => $servicePrinter->id,
            'technician_id' => $carlos->id,
            'performed_at' => '2026-09-20',
            'description' => 'Se recibió el dispositivo para revisión debido a problemas de impresión.',
        ]);
    }
}