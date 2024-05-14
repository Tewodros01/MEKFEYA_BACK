<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Setting\LocationModel;

class LocationController extends Controller{

  public function index()
  {
      try {
          $locations = LocationModel::all();
          return response($locations,200);
      } catch (\Exception $e) {
          return $this->handleException($e);
      }
  }

  public function create(Request $request)
  {
      try {
          $request->validate([
              'location_name' => 'required|string',
              'location_code' => 'required|string',
          ]);

          $location = new LocationModel();
          $location->location_name = $request->location_name;
          $location->location_code = $request->location_code;
          $location->save();

          return response()->json(['message' => 'Location created successfully']);
      } catch (\Exception $e) {
          return $this->handleException($e);
      }
  }

  public function show($id)
  {
      try {
          $location = LocationModel::findOrFail($id);
          return response()->json(['data' => $location]);
      } catch (ModelNotFoundException $e) {
          return response()->json(['error' => 'Location not found'], 404);
      } catch (\Exception $e) {
          return $this->handleException($e);
      }
  }

  public function update(Request $request,$id)
  {
      try {
          $request->validate([
              'id' => 'required|integer',
              'location_name' => 'required|string',
              'location_code' => 'required|string',
          ]);

          $location = LocationModel::findOrFail($id);
          $location->location_name = $request->location_name;
          $location->location_code = $request->location_code;
          $location->save();

          return response()->json(['message' => 'Location updated successfully']);
      } catch (ModelNotFoundException $e) {
          return response()->json(['error' => 'Location not found'], 404);
      } catch (\Exception $e) {
          return $this->handleException($e);
      }
  }

  public function delete($id)
  {
      try {
          $location = LocationModel::findOrFail($id);
          $location->delete();

          return response()->json(['message' => 'Location deleted successfully']);
      } catch (ModelNotFoundException $e) {
          return response()->json(['error' => 'Location not found'], 404);
      } catch (\Exception $e) {
          return $this->handleException($e);
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