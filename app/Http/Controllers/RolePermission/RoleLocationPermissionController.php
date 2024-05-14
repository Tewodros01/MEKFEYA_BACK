<?php

namespace App\Http\Controllers\RolePermission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RolePermission\RoleLocationPermissionModel;
use Illuminate\Validation\Rule;

class RoleLocationPermissionController extends Controller
{
    public function index()
    {
        try {
            // Retrieve all user location permissions
            $userLocationPermissions = RoleLocationPermissionModel::all();
            return response()->json($userLocationPermissions);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function show($id)
    {
        try {
            // Retrieve a specific user location permission
            $userLocationPermission = RoleLocationPermissionModel::findOrFail($id);
            return response()->json($userLocationPermission);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User Location Permission not found'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validation
            $this->validate($request, [
                'role_id' => 'required|exists:roles,id',
                'location_id' => 'required|exists:locations,id',
                'permission_id' => 'required|exists:permissions,id',
            ]);

            // Check if the record already exists
            $existingRecord = RoleLocationPermissionModel::where([
                'role_id' => $request->role_id,
                'location_id' => $request->location_id,
                'permission_id' => $request->permission_id,
            ])->first();

            if ($existingRecord) {
                return response()->json(['error' => 'Record already exists'], 422);
            }

            // Store a new user location permission
            $userLocationPermission = RoleLocationPermissionModel::create($request->all());
            return response()->json($userLocationPermission, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Validation Failed', 'details' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validation
            $this->validate($request, [
                'user_id' => 'exists:users,id',
                'location_id' => 'exists:locations,id',
                'permission_id' => 'exists:permissions,id',
                'status' => 'required|string',
            ]);

            // Update a specific user location permission
            $userLocationPermission = RoleLocationPermissionModel::findOrFail($id);
            $userLocationPermission->update($request->all());
            return response()->json($userLocationPermission, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Validation Failed', 'details' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        try {
            // Delete a specific user location permission
            $userLocationPermission = RoleLocationPermissionModel::findOrFail($id);
            $userLocationPermission->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User Location Permission not found'], 404);
        }
    }

    public function destroyByParams(Request $request)
    {
        try {
            // Validation
            $this->validate($request, [
                'role_id' => 'required|exists:roles,id',
                'location_id' => 'required|exists:locations,id',
                'permission_id' => 'required|exists:permissions,id',
            ]);

            // Delete a specific user location permission based on role_id, location_id, and permission_id
            $userLocationPermission = RoleLocationPermissionModel::where([
                'role_id' => $request->role_id,
                'location_id' => $request->location_id,
                'permission_id' => $request->permission_id,
            ])->first();

            if ($userLocationPermission) {
                $userLocationPermission->delete();
                return response()->json(null, 204);
            } else {
                return response()->json(['error' => 'User Location Permission not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Validation Failed', 'details' => $e->getMessage()], 422);
        }
    }

    public function postRoleLocationPermissionsBatch(Request $request)
    {
        try {
            // Validation
            $this->validate($request, [
                '*.role_id' => 'required|exists:roles,id',
                '*.location_id' => 'required|exists:locations,id',
                '*.permission_id' => 'required|exists:permissions,id',
            ]);

            // Extract unique changes based on role_id, location_id, and permission_id
            $uniqueChanges = collect($request->all())->unique();

            // Create role location permissions in a single batch
            RoleLocationPermissionModel::insert($uniqueChanges->toArray());

            return response()->json(['message' => 'Role location permissions created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Validation Failed', 'details' => $e->getMessage()], 422);
        }
    }

    public function deleteRoleLocationPermissionsBatch(Request $request)
    {
        try {
            // Validation
            $this->validate($request, [
                '*.role_id' => 'required|exists:roles,id',
                '*.location_id' => 'required|exists:locations,id',
                '*.permission_id' => 'required|exists:permissions,id',
            ]);

            // Extract role_ids, location_ids, and permission_ids from the request
            $requestData = $request->json()->all();

            $roleIds = collect($requestData)->pluck('role_id');
            $locationIds = collect($requestData)->pluck('location_id');
            $permissionIds = collect($requestData)->pluck('permission_id');

            // Check if any records match the given criteria
            $existingRoleLocationPermissions = RoleLocationPermissionModel::whereIn('role_id', $roleIds)
                ->whereIn('location_id', $locationIds)
                ->whereIn('permission_id', $permissionIds)
                ->get();

            if ($existingRoleLocationPermissions->isEmpty()) {
                return response()->json(['error' => 'No matching records found'], 404);
            }

            // Delete role location permissions in a single batch
            $deletedCount = RoleLocationPermissionModel::whereIn('role_id', $roleIds)
                ->whereIn('location_id', $locationIds)
                ->whereIn('permission_id', $permissionIds)
                ->delete();

            if ($deletedCount > 0) {
                return response()->json(['message' => 'Role location permissions deleted successfully'], 204);
            } else {
                return response()->json(['error' => 'Deletion Failed. No records deleted.'], 422);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Deletion Failed', 'details' => $e->getMessage()], 422);
        }
    }

}
