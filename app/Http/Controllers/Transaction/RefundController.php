<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use app\Models\Product\ProductModel;
use App\Helpers\Helper;
use App\Models\Transaction\RefundModel;
use App\Models\Transaction\TransactionDetailModel;
use App\Models\Transaction\TransactionModel;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class RefundController extends Controller
{
    // access all refund transactions
    public function index()
    {
        $refunds = RefundModel::with(['transaction.customer', 'transactionDetails.product.taxRate','refundBy', 'location'])
            ->get();
            return $refunds;
    }

    public function create(Request $request){
        $validator = Validator(
            $request->all(),
            ['transactionId' => 'required'],
            ['productId' => 'required'],
            ['refundBy' => 'required'],
            ['locationId' => 'required'],
            ['refundDate' => 'required'],
            ['refundedAmount' => 'required'],
            ['invoiceNo' => 'required'],
            ['refundReson' => 'required'],
        );
        if ($validator->fails()) {
            $errors = $validator->errors()->getMessages();
            return response()->json($errors, 417);
        }

        try {
            DB::beginTransaction();

            $rfNo = substr(Helper::IDGenerator(new RefundModel(), 'rf_no', '8', ''),1);
            $now = Carbon::now()->toDateTimeString();
            $data = Carbon::createFromFormat('Y-m-d H:i:s', $now, 'UTC')
                ->setTimezone('Africa/Addis_Ababa');
            $curentTime = $data->toDateTimeString();
            $refundIds = [];
            if ($request) {

                for ($i = 0; $i < count($request->transactiondetailId); $i++) {
                    $transactionDetail =TransactionDetailModel::with('Product')
                        ->where('transaction_id', $request->transactionId)
                        ->where('id', $request->transactiondetailId[$i])->first();
                    $refundmodel = new RefundModel();
                    $refundmodel->rf_no = $rfNo;
                    $refundmodel->transaction_id = $request->transactionId;
                    $refundmodel->transactiondetail_id = $request->transactiondetailId[$i];
                    $refundmodel->refund_by = $request->refundBy;
                    $refundmodel->location_id  = $request->locationId;
                    $refundmodel->refund_date = $request->refundDate;
                    $refundmodel->refunded_amount = $request->refundedAmount;
                    $refundmodel->refunded_in = $request->refundedIn;
                    $refundmodel->invoice_no = $request->invoiceNo;
                    $refundmodel->refund_reson = $request->refundReson;
                    $refundmodel->save();

                    $refundIds[] = $refundmodel->id;

                    $transactionDetail->is_refunded = 1;
                    $transactionDetail->update();


                    $itemsTo = ProductModel::where('id', $transactionDetail->product->id)
                        ->where('location_id', $request->locationId)
                        ->first();

                    $itemQuantityRefunded =$itemsTo->product_quantity + $transactionDetail->quantity_sold;
                    $itemsTo =  $itemsTo->update([
                        'product_quantity' => $itemQuantityRefunded,
                    ]);
                }
                if ($transactionDetail &&  $refundmodel &&   $itemsTo ) {

                    DB::commit();
                    $refund = RefundModel::with(['transaction','transactionDetails.product.taxRate','refundBy','location'])
                        ->whereIn('refunds.id', $refundIds)
                        ->get();
                        return  response($refund, 200);
                }
            }
        } catch (\Throwable $e) {
            return response($e,500);
        }
    }
    public function show($id){
        try {
            if ($id) {
                $refundTransaction = RefundModel::with(['transaction.customer', 'transactionDetails.product.taxRate', 'refundBy', 'location'])
                    ->where('id', $id)
                    ->get();
                return $refundTransaction;
            }
        } catch (\Exception $e) {
            return response()->json(['error_message' => $e->getMessage()], 500);
        }
    }

    public function showByRfNo($rf_no)
    {
        try {
            if ($rf_no) {
                // Use eager loading to retrieve related data
                $refundTransaction = RefundModel::with(['transaction.customer', 'transactionDetails.product.taxRate', 'refundBy', 'location'])
                    ->where('rf_no', $rf_no)
                    ->get();

                return $refundTransaction;
            }
        } catch (\Exception $e) {
            return response()->json(['error_message' => $e->getMessage()], 500);
        }
    }
}
