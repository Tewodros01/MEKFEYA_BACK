<?php

namespace App\Http\Controllers\RolePermission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RolePermission\RolePermissionModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class RolePermissionController extends Controller
{
    public function create(Request $request)
    {
        try {
            $request->validate([
                'role_id' => 'required|exists:roles,id',
                'permission_id' => 'required|exists:permissions,id',
                'status' => 'integer',
            ]);

            // Check if the role already has the permission
            $existingRolePermission = RolePermissionModel::where([
                'role_id' => $request->role_id,
                'permission_id' => $request->permission_id,
            ])->first();

            if ($existingRolePermission) {
                return response()->json(['error' => 'Role already has the permission'], 422);
            }

            // If the role doesn't have the permission, create it
            $rolePermission = RolePermissionModel::create($request->all());

            return response()->json(['data' => $rolePermission], 201);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function index()
    {
        try {
            $rolePermissions = RolePermissionModel::all();
            return response()->json(['data' => $rolePermissions]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show($id)
    {
        try {
            $rolePermission = RolePermissionModel::findOrFail($id);
            return response()->json(['data' => $rolePermission]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Role permission not found'], 404);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'role_id' => 'required|exists:roles,id',
                'permission_id' => 'required|exists:permissions,id',
                'status' => 'integer',
            ]);

            $rolePermission = RolePermissionModel::findOrFail($id);
            $rolePermission->update($request->all());

            return response()->json(['data' => $rolePermission]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Role permission not found'], 404);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function delete($id)
    {
        try {
            $rolePermission = RolePermissionModel::findOrFail($id);
            $rolePermission->delete();

            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Role permission not found'], 404);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyByParams(Request $request)
    {
        try {
            // Validation
            $this->validate($request, [
                'role_id' => 'required|exists:roles,id',
                'permission_id' => 'required|exists:permissions,id',
            ]);

            // Delete a specific role  permission based on role_id, location_id, and permission_id
            $rolePermission = RolePermissionModel::where([
                'role_id' => $request->role_id,
                'permission_id' => $request->permission_id,
            ])->first();

            if ($rolePermission) {
                $rolePermission->delete();
                return response()->json(['success' => 'Role Permission Deleted Success'], 204);
            } else {
                return response()->json(['error' => 'Role  Permission not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Validation Failed', 'details' => $e->getMessage()], 422);
        }
    }

    public function postRolePermissionsBatch(Request $request)
    {
        try {
            // Validation
            $this->validate($request, [
                '*.role_id' => 'required|exists:roles,id',
                '*.permission_id' => 'required|exists:permissions,id',
                '*.status' => 'integer',
            ]);

            // Check if the permissions already exist for the roles
            $requestData = collect($request->all());
            $existingRolePermissions = RolePermissionModel::whereIn('role_id', $requestData->pluck('role_id'))
                ->whereIn('permission_id', $requestData->pluck('permission_id'))
                ->get();

            if ($existingRolePermissions->isNotEmpty()) {
                return response()->json(['error' => 'One or more role permissions already exist'], 422);
            }

            // If no existing role permissions found, create them
            RolePermissionModel::insert($requestData->unique()->toArray());

            return response()->json(['message' => 'Role permissions created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Validation Failed', 'details' => $e->getMessage()], 422);
        }
    }

    public function deleteRolePermissionsBatch(Request $request)
    {
        try {
            // Validation
            $this->validate($request, [
                '*.role_id' => 'required|exists:roles,id',
                '*.permission_id' => 'required|exists:permissions,id',
            ]);

            // Extract role_ids and permission_ids from the request
            $requestData = $request->json()->all();

            $roleIds = collect($requestData)->pluck('role_id');
            $permissionIds = collect($requestData)->pluck('permission_id');

            // Delete role permissions in a single batch
            $deleted = RolePermissionModel::whereIn('role_id', $roleIds)
                ->whereIn('permission_id', $permissionIds)
                ->delete();

            if ($deleted) {
                return response()->json(['message' => 'Role permissions deleted successfully'], 204);
            } else {
                return response()->json(['error' => 'Deletion Failed. No records deleted.'], 422);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Deletion Failed', 'details' => $e->getMessage()], 422);
        }
    }

    /**
     * Handle exceptions in a uniform way.
     *
     * @param \Exception $e
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleException(\Exception $e)
    {
        return response()->json([
            'error' => 'An error occurred while processing the request.',
            'exception_message' => $e->getMessage(),
        ], 500);
    }
}

