<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Customer\CustomerModel;

class CustomerController extends Controller
{
    public function index()
    {
        try {
            $customers = CustomerModel::all();
            return response($customers,200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create(Request $request)
    {
        try {
            $this->validateRequest($request);

            $customer = CustomerModel::create($request->all());

            return response()->json($customer, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validateUpdateRequest($request);

            $customer = CustomerModel::findOrFail($id);
            $customer->update($request->all());

            return response()->json($customer, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $customer = CustomerModel::findOrFail($id);
            return response()->json($customer);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $customer = CustomerModel::findOrFail($id);
            $customer->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function validateRequest(Request $request)
    {
        $this->validate($request, [
            'full_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'tin_number' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email,' . ($request->id ?? ''),
            'vat_reg_number' => 'nullable|string|max:255',
        ]);
    }

    private function validateUpdateRequest(Request $request)
    {
        $this->validate($request, [
            'full_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'tin_number' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'vat_reg_number' => 'nullable|string|max:255',
        ]);
    }

}
