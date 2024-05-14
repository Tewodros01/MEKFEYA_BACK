<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UnitOfMeasurement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Setting\UnitOfMeasurementModel;

class UnitOfMeasurementController extends Controller
{
    public function index()
    {
        $units = UnitOfMeasurementModel::all();
        return response($units,200);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'measure' => 'required|string|max:250',
                'unit' => 'required|numeric',
            ]);

            $unit = UnitOfMeasurementModel::create($validatedData);

            return response()->json($unit, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $unit = UnitOfMeasurementModel::findOrFail($id);
        return response()->json($unit);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'measure' => 'required|string|max:250',
            'unit' => 'required|numeric',
        ]);

        try {
            $unit = UnitOfMeasurementModel::findOrFail($id);
            $unit->update($validatedData);
            return response()->json($unit);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update unit of measurement'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $unit = UnitOfMeasurementModel::findOrFail($id);
            $unit->delete();
            return response()->json(['message' => 'Unit of measurement deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete unit of measurement'], 500);
        }
    }
}
