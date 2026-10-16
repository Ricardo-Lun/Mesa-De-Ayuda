<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return response()->json(Service::all(), 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'folio' => [
                'required',
                'string',
                'max:30',
                'unique:services,folio',
            ],
            'device_id' => [
                'required',
                'integer',
                'exists:devices,id',
            ],
            'technician_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'status' => [
                'required',
                'string',
                'in:pending,in_progress,waiting_for_parts,completed,cancelled',
            ],
        ]);

        $service = Service::create($data);

        return response()->json($service, 201);
    }

    public function show(Service $service)
    {
        return response()->json($service, 200);
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'folio' => [
                'sometimes',
                'required',
                'string',
                'max:30',
                'unique:services,folio,' . $service->id,
            ],
            'device_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:devices,id',
            ],
            'technician_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:users,id',
            ],
            'status' => [
                'sometimes',
                'required',
                'string',
                'in:pending,in_progress,waiting_for_parts,completed,cancelled',
            ],
        ]);

        $service->update($data);

        return response()->json($service->fresh(), 200);
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return response()->noContent();
    }
}