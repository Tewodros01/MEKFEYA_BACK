<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product\ProductCategoryModel;
use App\Helpers\Helper;
use Validator;
use Illuminate\Support\Facades\Cache;

class ProductCategoryController extends Controller
{
    // Index - Get all categories
    public function index()
    {
        try {
            $categories = Cache::remember('product_categories', 60, function () {
                return ProductCategoryModel::all();
            });

            return response($categories,200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Show - Get a specific category
    public function show($id)
    {
        try {
            $category = Cache::remember("product_category_{$request->id}", 60, function () use ($request) {
                return ProductCategoryModel::find($id);
            });

            if (!$category) {
                return response()->json(['error' => 'Category not found'], 404);
            }

            return response()->json($category);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Create - Store a new category
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_name' => 'required|max:255',
                'cat_code' => 'required|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $category = ProductCategoryModel::create($request->all());

            // Clear the cached categories after creating a new one
            Cache::forget('product_categories');

            return response()->json($category, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Update - Update a category
    public function update(Request $request, $id)
    {
        try {
            $category = ProductCategoryModel::find($id);

            if (!$category) {
                return response()->json(['error' => 'Category not found'], 404);
            }

            $validator = Validator::make($request->all(), [
                'category_name' => 'required|max:255',
                'cat_code' => 'required|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $category->update($request->all());

            // Clear the cached category after updating
            Cache::forget("product_category_{$request->id}");
            Cache::forget('product_categories');

            return response()->json($category, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // Delete - Delete a category
    public function delete($id)
    {
        try {
            $category = ProductCategoryModel::find($id);

            if (!$category) {
                return response()->json(['error' => 'Category not found'], 404);
            }

            $category->delete();

            // Clear the cached category after deletion
            Cache::forget("product_category_{$request->id}");
            Cache::forget('product_categories');

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
