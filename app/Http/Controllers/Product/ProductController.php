<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use App\Models\Product\ProductModel;
use App\Models\Transaction\TransactionDetailModel;
use App\Helpers\Helper;

class ProductController extends Controller
{
    //
    public function index()
    {
        $products =  ProductModel::with(['category','unit', 'location', 'taxRate', 'regBy'])->get();
        return $products;
    }

    public function productIndex()
    {
        try {
            $products = ProductModel::with(['category', 'unit', 'location', 'taxRate', 'regBy'])->get();
            $productCount = $products->count();

            return response()->json([
                'products' => $products,
                'product_count' => $productCount,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving products', 'error_message' => $e->getMessage()], 500);
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();

        try {
            $umlCode  = Helper::IDGenerator(new ProductModel(), 'prc_code', 5, '');
            $validator = validator(
                $request->all(),
                [
                    'productDescription' => 'required',
                    'productCategoryId' => 'required',
                    'unitCost' => 'required',
                    'unitPrice' => 'required',
                    'productQuantity' => 'required',
                    'taxRateId' => 'required',
                    'locationId' => 'required',
                    'regById' => 'required',
                ]
            );
            if ($validator->fails()) { // if validation fails return error message
                $errors = $validator->errors()->getMessages();
                return response()->json($errors, 417);
            }
            if ($request->hasFile('image')) {
                $name = $request->file('image')->getClientOriginalName();
                $request->image->move(public_path('itemimage'), $name);
                $path = '../../itemimage/' . $name;
            } else {
                $name = "";
                $path = "";
            }
            $product = new ProductModel();
            $product->product_description = $request->productDescription;
            $product->product_code = $request->productCode;
            $product->prc_code = ltrim($umlCode, '-');
            $product->product_category_id = $request->productCategoryId;
            $product->unit_cost = $request->unitCost;
            $product->unit_of_measurement = $request->UOM;
            $product->unit_price = $request->unitPrice;
            $product->product_quantity = $request->productQuantity;
            $product->product_image = $name;
            $product->productimage_path = $path;
            $product->tax_rate_id = $request->taxRateId;
            $product->remark = $request->remark;
            $product->is_active = $request->isActive;
            $product->location_id = $request->locationId;
            $product->reg_by_id = $request->regById;
            $product->purchase_date = $request->purchaseDate;
            $product->exp_date = $request->expDate;
            $product->save();
            if ($product) {
                $id = $product->id;
                DB::commit();
                return response()->json(ProductModel::find($id), 201);
            } else {
                DB::rollback();
            }
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'error' => 'theire is an error to perform this operation',
                'exception_message' => $th->getMessage()
            ], 500);
        }
    }

    public function getAvailableProducts()
    {
        try {
            $availableProducts = ProductModel::with(['category', 'unit', 'location', 'taxRate', 'regBy'])
                ->where('product_quantity', '>', 0)
                ->where('is_active', true)
                ->get();

            return response($availableProducts, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving available products', 'error_message' => $e->getMessage()], 500);
        }
    }

    public function getExpiredProducts()
    {
        try {
            $expiredProducts = ProductModel::with(['category', 'unit', 'location', 'taxRate', 'regBy'])
                ->where('exp_date', '<', now()) // Assuming 'exp_date' is in datetime format
                ->get();

            return response($expiredProducts, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving expired products', 'error_message' => $e->getMessage()], 500);
        }
    }

    public function getExpiringProductsWithThreeMonth()
    {
        try {
            $today = now();
            $threeMonthsFromNow = now()->addMonths(3);

            $expiringProducts = ProductModel::with(['category', 'unit', 'location', 'taxRate', 'regBy'])
                ->whereBetween('exp_date', [$today, $threeMonthsFromNow])
                ->get();

            return response($expiringProducts, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving expiring products', 'error_message' => $e->getMessage()], 500);
        }
    }

    public function getProductsExpiringIn3to6Months()
    {
        try {
            $threeMonthsFromNow = now()->addMonths(3);
            $sixMonthsFromNow = now()->addMonths(6);

            $expiringProducts = ProductModel::with(['category', 'unit', 'location', 'taxRate', 'regBy'])
                ->whereBetween('exp_date', [$threeMonthsFromNow, $sixMonthsFromNow])
                ->get();

            return response($expiringProducts, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving products expiring in 3 to 6 months', 'error_message' => $e->getMessage()], 500);
        }
    }

    public function getExpiredAndExpiringProducts()
    {
        try {
            $today = now();
            $sixMonthsFromNow = now()->addMonths(6);

            $expiredAndExpiringProducts = ProductModel::with(['category', 'unit', 'location', 'taxRate', 'regBy'])
                ->where(function ($query) use ($today) {
                    // Products already expired
                    $query->where('exp_date', '<', $today);
                })
                ->orWhereBetween('exp_date', [$today, $sixMonthsFromNow])
                ->get();

            return response($expiredAndExpiringProducts, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving expired and expiring products', 'error_message' => $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        try {
            if ($id) {
                $item = ProductModel::with(['category','unit', 'taxRate', 'regBy', 'location'])
                    ->where('id', $id)
                    ->get();
                return $item;
            }
        } catch (\Exception $e) {
            return response()->json(['error_message' => $e->getMessage()], 500);
        }
    }
    public function getByLocation($location)
    {
        try {
            if ($location) {
                $item = ProductModel::with(['category','unit', 'taxRate', 'regBy', 'location'])
                    ->where('location_id', $location)
                    ->where('product_quantity', '>', '0')
                    ->get();
                return $item;
            }
        } catch (\Exception $e) {
            return response()->json(['error_message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        $itemId = $id;
        $item = ProductModel::find($itemId);

        $performedTransaction = TransactionDetailModel::where('product_id', $itemId)->get();
        if (count($performedTransaction) <= 0) {
            try {
                $umlCode = $item->prc_code;
                $itemImage = ProductModel::select('product_image')
                    ->where('id', $item->id)->value('product_image') | "";
                $itemImagePath = ProductModel::select('productimage_path')->where('id', $itemId)->value('productimage_path') | "";
                if ($request->hasFile('image')) {

                    if ($item->product_image != " ") {
                        File::delete(public_path("itemimage/" . $item->product_image));
                    }
                    $itemImage = $request->file('image')->getClientOriginalName();
                    $request->image->move(public_path('itemimage'), $itemImage);
                    $itemImagePath = '../../itemimage/' . $itemImage;
                } else if (!($itemImagePath && $itemImage)) {
                    $itemImage = "";
                    $itemImagePath = "";
                }
                $item->product_description = $request->productDescription;
                $item->product_code = $request->productCode;
                $item->prc_code = ltrim($umlCode, '-');
                $item->product_category_id = $request->productCategoryId;
                $item->unit_cost = $request->unitCost;
                $item->unit_of_measurement = $request->UOM;
                $item->unit_price = $request->unitPrice;
                $item->product_quantity = $request->productQuantity;
                $item->product_image = $itemImage;
                $item->productimage_path = $itemImagePath;
                $item->tax_rate_id = $request->taxRateId;
                $item->remark = $request->remark;
                $item->is_active = $request->isActive;
                $item->location_id = $request->locationId;
                $item->reg_by_id = $request->regById;
                $item->purchase_date = $request->purchaseDate;
                $item->exp_date = $request->expDate;
                $item->update();
                $itemId = $item->id;
                if ($item) {
                    DB::commit();
                    $updatedItem = ProductModel::with(['category','unit', 'location', 'taxRate', 'regBy'])->where('id', $itemId)->get();
                    return response()->json($updatedItem, 200);
                }
            } catch (\Throwable $th) {
                DB::rollback();
                return response()->json([
                    'errors' => 'there is a problem transaction not completed',
                    'exception_message' => $th->getMessage(),
                ], 500);
            };
        }
    }

    public function delete($id)
    {
        try {
            if ($id) {
                $itemToDelete = ProductModel::findOrFail($id);
                $itemToDelete->delete();

                return response()->json(['success' => 'item deleted successfully'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'error deleting item from items list'], 200);
        }
    }
}
