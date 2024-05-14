<?php

namespace app\Http\Controllers\RolePermission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\RolePermission\RoleModel;
use App\Models\RolePermission\RolePermissionModel;
use App\Models\RolePermission\PermissionModel;
use DB;

class PermissionController extends Controller
{

    public function create(Request $request)
    {
        try {
            $request->validate([
                'permission_name' => 'required|string',
            ]);

            // Create a new permission
            $permission = new PermissionModel();
            $permission->permission_name = $request->permission_name;
            $permission->save();

            return response()->json(['message' => 'Permission created successfully', 'permission' => $permission], 201);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return response()->json([
                'error' => $validationException->validator->errors()->first(),
            ], 422); // Unprocessable Entity
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'An error occurred while processing the request.',
                'exception_message' => $th->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        try {
            // Get all permissions
            $permissions = PermissionModel::all();

            return response()->json(['permissions' => $permissions], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'An error occurred while processing the request.',
                'exception_message' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'permission_name' =>'required|string',
            ]);

            // Find the permission
            $permission = PermissionModel::findOrFail($id);

            // Update the permission
            $permission->permission_name = $request->permission_name;
            $permission->save();

            return response()->json(['message' => 'Permission updated successfully', 'permission' => $permission], 200);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return response()->json([
                'error' => $validationException->validator->errors()->first(),
            ], 422); // Unprocessable Entity
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'An error occurred while processing the request.',
                'exception_message' => $th->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            // Find the permission
            $permission = PermissionModel::findOrFail($id);

            // Soft delete the permission
            $permission->delete();

            return response()->json(['message' => 'Permission deleted successfully'], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'An error occurred while processing the request.',
                'exception_message' => $th->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            // Find the permission
            $permission = PermissionModel::findOrFail($id);

            return response()->json(['permission' => $permission], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'An error occurred while processing the request.',
                'exception_message' => $th->getMessage(),
            ], 500);
        }
    }
}
