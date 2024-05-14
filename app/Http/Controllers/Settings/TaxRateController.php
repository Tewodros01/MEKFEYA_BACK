<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Setting\TaxRateModel;

class TaxRateController extends Controller
{
    // Index - Get all tax rates
    public function index()
    {
        try {
            $taxRates = TaxRateModel::all();
            return response($taxRates,200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Show - Get a specific tax rate
    public function show($id)
    {
        try {
            $taxRate = TaxRateModel::find($id);

            if (!$taxRate) {
                return response()->json(['error' => 'Tax rate not found'], 404);
            }

            return response()->json($taxRate);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Create - Store a new tax rate
    public function create(Request $request)
    {
        try {
            $request->validate([
                'tax_type' => 'required|max:255',
                'tax_rate' => 'required|numeric|between:0,100',
            ]);

            $taxRate = TaxRateModel::create($request->all());

            return response()->json($taxRate, 201);
        } catch (ValidationException $validationException) {
            return response()->json(['error' => $validationException->errors()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Update - Update a tax rate
    public function update(Request $request, $id)
    {
        try {
            $taxRate = TaxRateModel::find($id);

            if (!$taxRate) {
                return response()->json(['error' => 'Tax rate not found'], 404);
            }

            $request->validate([
                'tax_type' => 'required|max:255',
                'tax_rate' => 'required|numeric|between:0,100',
            ]);

            $taxRate->update($request->all());

            return response()->json($taxRate, 200);
        } catch (ValidationException $validationException) {
            return response()->json(['error' => $validationException->errors()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Delete - Delete a tax rate
    public function delete($id)
    {
        try {
            $taxRate = TaxRateModel::find($id);

            if (!$taxRate) {
                return response()->json(['error' => 'Tax rate not found'], 404);
            }

            $taxRate->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
