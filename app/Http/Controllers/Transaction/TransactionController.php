<?php

namespace App\Http\Controllers\Transaction;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Product\ProductModel;
use App\Models\Transaction\TransactionModel;
use App\Models\Transaction\TransactionDetailModel;
use App\Models\Transaction\PaymentsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{

    public function index()
    {
        $allTransactions = TransactionModel::with(['salesBy', 'customer', 'payment', 'location'])
            ->get();
        return  $allTransactions;
    }

    /* create sales transactions  */
    public function create(Request $request)
    {
        $validiator  =  Validator(
            $request->all(),
            [
                'userId' => 'required',
                'itemPrice' => 'required',
                'totalAmount' => 'required',
                'transactionDate' => 'required',
                'locationId' => 'required',
                'quantitySold' => 'required',
            ]
        );
        if ($validiator->fails()) {
            $errors = [
                'error' => 'the datas are not valied fill all requireds',
                'error_message' => $validiator->errors()->getMessages()
            ];
            return response()->json($errors, 417);
        }
        try {
            DB::beginTransaction();
            $fsNo = substr(Helper::IDGenerator(new TransactionModel(), 'fs_no', '8', ''), 1);
            $now = Carbon::now()->toDateTimeString();
            $currentDate = Carbon::createFromFormat('Y-m-d H:i:s', $now, 'UTC')->setTimezone('Africa/Addis_Ababa')->toDateTimeString();

            if ($request->salesType == 'cash') {
                $invoiceNo = Helper::IDGenerator(new TransactionModel(), 'invoice_no', '6', 'CSI');
            } else {
                $invoiceNo = Helper::IDGenerator(new TransactionModel(), 'invoice_no', '6', 'CRI');
            }

            $transaction = new TransactionModel();
            $transaction->user_id = $request->userId;
            $transaction->customer_id = $request->customerId;
            $transaction->total_amount = $request->totalAmount;
            $transaction->transaction_date = $request->transactionDate;
            $transaction->invoice_no = $invoiceNo;
            $transaction->sales_type = $request->salesType;
            $transaction->fs_no = $fsNo;
            $transaction->total_vat = $request->totalVat;
            $transaction->total_discount = $request->Totaldiscount;
            $transaction->change_amount = $request->changeAmount;
            $transaction->is_void = $request->isVoid;
            $transaction->void_remark = $request->voidRemark;
            $transaction->location_id = $request->locationId;
            $transaction->is_refunded = $request->isRefunded;

            $transaction->save();
            $transactionId = $transaction->id;

            for ($i = 0; $i < count($request->paymentModes); $i++) {
                $emptyArray = [null];
                $paidAmount = $request->paidTotal[$i];
                $paymentTransactionNo = $request->paymentTransactionNo[$i];
                if ($request->paymentModes[$i] === 'cash') {
                    $paymentMode = $request->paymentModes[$i];
                } else if ($request->paymentModes[$i] === 'cheque') {
                    $paymentMode = $request->paymentModes[$i];
                } else  if ($request->paymentModes[$i] == 'banktobank') {
                    $paymentMode = $request->paymentModes[$i];
                } else  if ($request->paymentModes[$i] == 'mobilebank') {
                    $paymentMode = $request->paymentModes[$i];
                } else  if ($request->paymentModes[$i] == 'Credit') {
                    $paymentMode = $request->paymentModes[$i];
                }
                $payment = PaymentsModel::create([
                    'payment_mode' =>  $paymentMode,
                    'paid_amount' =>  $paidAmount,
                    'payment_transaction_no' =>  $paymentTransactionNo,
                    'transaction_id' => $transactionId,
                ]);
            }

            for ($i = 0; $i < count($request->productId); $i++) {
                $item = ProductModel::find($request->productId[$i]);

                if ($item->product_quantity >= $request->quantitySold[$i]) {
                    $itemQuantityRemain = $item->product_quantity - $request->quantitySold[$i];
                    $item->product_quantity = $itemQuantityRemain;
                    $item->save();

                    $transactionDetail = TransactionDetailModel::create([
                        'transaction_id' => $transactionId,
                        'product_id' => $request->productId[$i],
                        'quantity_sold' => $request->quantitySold[$i],
                        'item_price' => $request->itemPrice[$i],
                        'subtotal' => $request->subTotal[$i],
                        'discount' =>  $request->discount[$i],
                    ]);
                } else {
                    return back()->with('error', 'Error Available quantity is less than requested!!!!!!!!');
                }
            }
            if ($transaction && $item && $transactionDetail && $payment) {

                DB::commit();

                return   $transactionDone = TransactionModel::with(['salesBy', 'transactionDetails.product', 'customer', 'payment', 'location'])
                    ->where('id', $transaction->id)
                    ->get();
            } else {

                DB::rollback();
                return back()->with('error', 'Error!!!!!!!!');
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'there  was an error to this transaction', 'error_message' => $e->getMessage()], 500);
        }
    }
    /**
     *  this function is  to void a sales transaction
     *  @param string $id
     * @return  voided transaction
     */

    public function voidTransaction(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $now = Carbon::now()->toDateTimeString();
            $voidDate = Carbon::createFromFormat('Y-m-d H:i:s', $now, 'UTC')
               ->setTimezone('Africa/Addis_Ababa');
            $user = Auth::user();


            // void sales
            $sales = TransactionModel::where('id', $id)->first();
            $sales->is_void =  1;
            $sales->void_date = $voidDate->toDateTimeString();
            $sales->void_by = $request->voidById;
            $sales->void_remark = $request->voidRemark;
            $sales->update();

            // void transaction

            $transactiondetail = TransactionDetailModel::where('transaction_id', $id)->get();
            for ($i = 0; $i < count($transactiondetail); $i++) {

               $Itemsto = ProductModel::where('id', $transactiondetail[$i]->product_id)
                  ->where('location_id', $sales->location_id)
                  ->first();

               $Quantity = $Itemsto->product_quantity + $transactiondetail[$i]->quantity_sold;

               $Itemsto =  $Itemsto->update([
                  'product_quantity' => $Quantity,
               ]);
            }

            if ($transactiondetail ) {

               DB::commit();
               return response()->json('transction voided successfully');
            } else {

               DB::rollback();
               return ('Error!!!!!!!!');
            }
         } catch (\Throwable $th) {
            return response(['error' => 'something went wrong', 'message' => $th->getMessage()]);
         }
         return response()->json("Request Accepted Succefully");
    }

    public function show($id)
    {
        try {
            if ($id) {
                $transaction = TransactionModel::with(['salesBy', 'transactionDetails.product.taxRate', 'customer', 'payment', 'location'])
                    ->where('id', $id)
                    ->get();
                return array("transaction" => $transaction);
            }
        } catch (\Exception $e) {
            return response()->json(['error_message' => $e->getMessage()], 500);
        }
    }

    public function showDailySalsdProductByDateUserIDAndLocationId(Request $request)
    {
        try {
            $user_id = $request->user_id;
            $date = $request->date?? null;
            $location_id = $request->location_id;
            $start_date = $request->start_date ?? null;
            $end_date = $request->end_date ?? null;

            $query = DB::table('transactions')
                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->select(
                    'transaction_details.id as id',
                    'products.product_description as product_name',
                    'transactions.invoice_no',
                    'transactions.total_amount as paid_amount',
                    'transaction_details.quantity_sold',
                    'locations.location_name as location_name',
                    'users.first_name as sales_person',
                    'transactions.transaction_date'
                )
                ->join('users', 'transactions.user_id', '=', 'users.id')
                ->join('locations', 'transactions.location_id', '=', 'locations.id')
                ->where('transactions.user_id', $user_id)
                ->where('transactions.location_id', $location_id);

            if ($date) {
                $query->whereDate('transactions.transaction_date', '=', Carbon::parse($date)->format('Y-m-d'));
            }

            if ($start_date && $end_date) {
                $query->whereBetween('transactions.transaction_date', [Carbon::parse($start_date)->format('Y-m-d'), Carbon::parse($end_date)->format('Y-m-d')]);
            }

            $transactions = $query->get();

            return $transactions;
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function showDailySalsProductByDate(Request $request)
    {
        try {
            $date = $request->date;
            $location_id = $request->location_id;

            $transactions = DB::table('transactions')
                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->select(
                    'products.product_description as product_name',
                    'transactions.invoice_no',
                    'transactions.total_amount as paid_amount',
                    'transaction_details.quantity_sold',
                    'locations.location_name as location_name',
                    'users.first_name as sales_person',
                    'transactions.transaction_date'
                )
                ->join('users', 'transactions.user_id', '=', 'users.id')
                ->join('locations', 'transactions.location_id', '=', 'locations.id')
                ->where('transactions.location_id', $location_id)
                ->whereDate('transactions.transaction_date', '=', Carbon::parse($date)->format('Y-m-d'))
                ->get();

            return $transactions;
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
