<?php

namespace App\Http\Controllers\RolePermission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RolePermission\RoleModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function create(Request $request)
    {
        try {
            $request->validate([
                'role_name' =>'required|string',
            ]);

            // Create a new role
            $role = new RoleModel();
            $role->role_name = $request->role_name;
            $role->save();

            return response()->json(['message' => 'Role created successfully', 'role' => $role], 201);
        } catch (ValidationException $validationException) {
            return $this->handleValidationException($validationException);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function indexWitPermission()
    {
        $roles = RoleModel::with('permissions')->get();

        return response()->json($roles);
    }

    public function index()
    {
        try {
            $roles = RoleModel::all();
            return response($roles,200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function indexRoles()
    {
        try {
            $roles = RoleModel::all();
            $roleCount = $roles->count();

            return response([
                'roles' => $roles,
                'role_count' => $roleCount,
            ], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'role_name' => 'required|string',
            ]);

            // Find the role by ID
            $role = RoleModel::findOrFail($id);

            // Update the role
            $role->role_name = $request->role_name;
            $role->save();

            return response()->json(['message' => 'Role updated successfully', 'role' => $role], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Role not found.'], 404);
        } catch (ValidationException $validationException) {
            return $this->handleValidationException($validationException);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function delete($id)
    {
        try {
            // Find the role by ID
            $role = RoleModel::findOrFail($id);

            // Check if there are any users associated with this role and handle as needed

            // Soft-delete the role
            $role->delete();

            return response()->json(['message' => 'Role deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Role not found.'], 404);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show($id)
    {
        try {
            // Find the role by ID
            $role = RoleModel::findOrFail($id);

            return response()->json(['role' => $role], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Role not found.'], 404);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Handle validation exceptions.
     *
     * @param \Illuminate\Validation\ValidationException $validationException
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleValidationException(ValidationException $validationException)
    {
        return response()->json([
            'error' => $validationException->validator->errors()->first(),
        ], 422); // Unprocessable Entity
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

