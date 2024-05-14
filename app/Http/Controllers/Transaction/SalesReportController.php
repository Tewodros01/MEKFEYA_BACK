<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class SalesReportController extends Controller
{
    public function getXDailySalesReport(Request $request)
    {
        try {
            // Validate the incoming request data
            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
                'location_id' => 'required|exists:locations,id',
                'user_id' => 'required|exists:users,id', // Add user_id validation
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Retrieve the date, location_id, and user_id from the request
            $date = $request->input('date');
            $locationId = $request->input('location_id');
            $userId = $request->input('user_id');

            // Fetch Z Daily Sales Report using a query
            $xDailySalesReport = DB::table('transactions')
                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->leftJoin('refunds', 'transactions.id', '=', 'refunds.transaction_id')
                ->leftJoin('tax_rates', 'products.tax_rate_id', '=', 'tax_rates.id')
                ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
                ->whereDate('transactions.transaction_date', '=', $date)
                ->where('transactions.location_id', '=', $locationId)
                ->where('transactions.user_id', '=', $userId) // Add user_id filter
                ->select([
                    'transactions.transaction_date',
                    'transactions.invoice_no',
                    'products.product_description',
                    'transaction_details.quantity_sold',
                    'transaction_details.item_price',
                    'transaction_details.subtotal',
                    'transaction_details.discount',
                    'refunds.refunded_amount',
                    'tax_rates.tax_type',
                    'tax_rates.tax_rate',
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                ])
                ->orderBy('transactions.transaction_date', 'asc')
                ->orderBy('transactions.invoice_no', 'asc')
                ->get();

            // Format and organize the data as needed
            $formattedReport = $this->formatDailyXReport($xDailySalesReport);

            // Return the formatted Z Daily Sales Report as a response
            return response()->json(['data' => $formattedReport]);

        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Additional method to format the X Daily Sales Report
    private function formatDailyXReport($xDailySalesReport)
    {
        // Initialize variables for total sales, total refunds, and tax totals
        $totalSales = 0;
        $totalRefunds = 0;
        $taxTotals = [];
        $refundTaxTotals = [];

        // Extract user data (since it's constant for the given user_id)
        $userData = $xDailySalesReport->isEmpty() ? null : [
            'first_name' => $xDailySalesReport->first()->first_name,
            'last_name' => $xDailySalesReport->first()->last_name,
            'email' => $xDailySalesReport->first()->email,
        ];

        // Iterate over the X Report data and organize it
        foreach ($xDailySalesReport as $item) {
            $taxType = $item->tax_type;
            $taxRate = $item->tax_rate;
            $quantitySold = $item->quantity_sold ?? 0;
            $subtotal = $item->subtotal ?? 0;
            $refundedAmount = $item->refunded_amount ?? 0;
            $isRefunded = $item->is_refunded ?? 0;

            // Calculate total sales and refunds
            $totalSales += $subtotal;
            $totalRefunds += $refundedAmount;

            // Organize tax totals
            if (!isset($taxTotals[$taxType])) {
                $taxTotals[$taxType] = ['rate' => $taxRate, 'total' => 0];
            }
            $taxTotals[$taxType]['total'] += $subtotal;

            // Organize refund tax totals for refunded products
            if ($isRefunded) {
                if (!isset($refundTaxTotals[$taxType])) {
                    $refundTaxTotals[$taxType] = ['rate' => $taxRate, 'total' => 0];
                }
                $refundTaxTotals[$taxType]['total'] += $refundedAmount;
            }
        }

        // Generate the formatted X Report
        $formattedXReport = [
            'user' => $userData,
            'totals' => [
                'salesTotal' => $totalSales,
                'refundTotal' => $totalRefunds,
            ],
            'taxTotals' => $taxTotals,
            'refundTaxTotals' => $refundTaxTotals,
        ];

        return $formattedXReport;
    }

    public function getXAccumulatedSalesReport(Request $request)
    {
        try {
            // Validate the incoming request data
            $validator = Validator::make($request->all(), [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'location_id' => 'required|exists:locations,id',
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Retrieve the start_date, end_date, location_id, and user_id from the request
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $locationId = $request->input('location_id');
            $userId = $request->input('user_id');

            // Fetch X Accumulated Sales Report using a query
            $xAccumulatedSalesReport = DB::table('transactions')
                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->leftJoin('refunds', 'transactions.id', '=', 'refunds.transaction_id')
                ->leftJoin('tax_rates', 'products.tax_rate_id', '=', 'tax_rates.id')
                ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
                ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
                ->where('transactions.location_id', '=', $locationId)
                ->where('transactions.user_id', '=', $userId)
                ->select([
                    'transactions.transaction_date',
                    'transactions.invoice_no',
                    'products.product_description',
                    'transaction_details.quantity_sold',
                    'transaction_details.item_price',
                    'transaction_details.subtotal',
                    'transaction_details.discount',
                    'refunds.refunded_amount',
                    'tax_rates.tax_type',
                    'tax_rates.tax_rate',
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                    'transaction_details.is_refunded', // Add this line to select is_refunded
                ])
                ->orderBy('transactions.transaction_date', 'asc')
                ->orderBy('transactions.invoice_no', 'asc')
                ->get();

            // Format and organize the data as needed
            $formattedReport = $this->formatXAccumulatedSalesReport($xAccumulatedSalesReport);

            // Return the formatted X Accumulated Sales Report as a response
            return response()->json(['data' => $formattedReport]);

        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Additional method to format the X Accumulated Sales Report
    private function formatXAccumulatedSalesReport($xAccumulatedSalesReport)
    {
        // Initialize variables for total sales, total refunds, and tax totals
        $totalSales = 0;
        $totalRefunds = 0;
        $taxTotals = [];
        $refundTaxTotals = []; // Added this line

        // Constant user data for X Accumulated Sales Report
        $userData = $xAccumulatedSalesReport->isEmpty() ? null : [
            'first_name' => $xAccumulatedSalesReport->first()->first_name,
            'last_name' => $xAccumulatedSalesReport->first()->last_name,
            'email' => $xAccumulatedSalesReport->first()->email,
        ];

        // Iterate over the X Accumulated Sales Report data and organize it
        // Iterate over the Z Accumulated Sales Report data and organize it
        foreach ($xAccumulatedSalesReport as $item) {
            $taxType = $item->tax_type;
            $taxRate = $item->tax_rate;
            $quantitySold = $item->quantity_sold ?? 0;
            $subtotal = $item->subtotal ?? 0;
            $refundedAmount = $item->refunded_amount ?? 0;
            $isRefunded = $item->is_refunded ?? 0;

            // Calculate total sales and refunds
            $totalSales += $subtotal;
            $totalRefunds += $refundedAmount;

            // Organize tax totals
            if (!isset($taxTotals[$taxType])) {
                $taxTotals[$taxType] = ['rate' => $taxRate, 'total' => 0];
            }
            $taxTotals[$taxType]['total'] += $subtotal;

            // Check if the product is refunded before including in refund tax totals
            if ($isRefunded) {
                // Organize refund tax totals
                if (!isset($refundTaxTotals[$taxType])) {
                    $refundTaxTotals[$taxType] = ['rate' => $taxRate, 'total' => 0];
                }
                $refundTaxTotals[$taxType]['total'] += $refundedAmount;
            }
        }
        // Generate the formatted X Accumulated Sales Report
        $formattedXAccumulatedSalesReport = [
            'user' => $userData,
            'totals' => [
                'salesTotal' => $totalSales,
                'refundTotal' => $totalRefunds,
            ],
            'taxTotals' => $taxTotals,
            'refundTaxTotals' => $refundTaxTotals,
            // Add other necessary information based on your requirements
        ];

        return $formattedXAccumulatedSalesReport;
    }

    public function getZDailySalesReport(Request $request)
    {
        try {
            // Validate the incoming request data
            $this->validate($request, [
                'date' => 'required|date',
                'location_id' => 'required|exists:locations,id',
            ]);

            // Retrieve the date and location_id from the request
            $date = $request->input('date');
            $locationId = $request->input('location_id');

            // Fetch Z Daily Sales Report using a query
            $zDailySalesReport = DB::table('transactions')
                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->leftJoin('refunds', 'transactions.id', '=', 'refunds.transaction_id')
                ->leftJoin('tax_rates', 'products.tax_rate_id', '=', 'tax_rates.id')
                ->whereDate('transactions.transaction_date', '=', $date)
                ->where('transactions.location_id', '=', $locationId)
                ->select([
                    'transactions.transaction_date',
                    'transactions.invoice_no',
                    'products.product_description',
                    'transaction_details.quantity_sold',
                    'transaction_details.item_price',
                    'transaction_details.subtotal',
                    'transaction_details.discount',
                    'refunds.refunded_amount',
                    'tax_rates.tax_type',
                    'tax_rates.tax_rate',
                ])
                ->orderBy('transactions.transaction_date', 'asc')
                ->orderBy('transactions.invoice_no', 'asc')
                ->get();

            // Format and organize the data as needed
            $formattedReport = $this->formatZDailySalesReport($zDailySalesReport);

            // Return the formatted Z Daily Sales Report as a response
            return response()->json(['data' => $formattedReport]);

        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Additional method to format the Z Daily Sales Report
    private function formatZDailySalesReport($zDailySalesReport)
    {
        // Initialize variables for total sales, total refunds, tax totals, and refund tax totals
        $totalSales = 0;
        $totalRefunds = 0;
        $taxTotals = [];
        $refundTaxTotals = [];

        // Iterate over the Z Report data and organize it
        foreach ($zDailySalesReport as $item) {
            $taxType = $item->tax_type;
            $taxRate = $item->tax_rate;
            $quantitySold = $item->quantity_sold ?? 0;
            $subtotal = $item->subtotal ?? 0;
            $refundedAmount = $item->refunded_amount ?? 0;
            $isRefunded = $item->is_refunded ?? 0;

            // Calculate total sales and refunds
            $totalSales += $subtotal;
            $totalRefunds += $refundedAmount;

            // Organize tax totals
            if (!isset($taxTotals[$taxType])) {
                $taxTotals[$taxType] = ['rate' => $taxRate, 'total' => 0];
            }
            $taxTotals[$taxType]['total'] += $subtotal;

            // Organize refund tax totals (only for refunded products)
            if ($isRefunded) {
                if (!isset($refundTaxTotals[$taxType])) {
                    $refundTaxTotals[$taxType] = ['rate' => $taxRate, 'total' => 0];
                }
                $refundTaxTotals[$taxType]['total'] += $refundedAmount;
            }
        }

        // Generate the formatted Z Report
        $formattedZReport = [
            'totals' => [
                'salesTotal' => $totalSales,
                'refundTotal' => $totalRefunds,
            ],
            'taxTotals' => $taxTotals,
            'refundTaxTotals' => $refundTaxTotals,
            // Add other necessary information based on your requirements
        ];

        return $formattedZReport;
    }


    public function getZAccumulatedSalesReport(Request $request)
    {
        try {
            // Validate the incoming request data
            $validator = Validator::make($request->all(), [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'location_id' => 'required|exists:locations,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Retrieve the start_date, end_date, and location_id from the request
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $locationId = $request->input('location_id');

            // Fetch Z Accumulated Sales Report using a query
            $zAccumulatedSalesReport = DB::table('transactions')
                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->leftJoin('refunds', 'transactions.id', '=', 'refunds.transaction_id')
                ->leftJoin('tax_rates', 'products.tax_rate_id', '=', 'tax_rates.id')
                ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
                ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
                ->where('transactions.location_id', '=', $locationId)
                ->select([
                    'transactions.transaction_date',
                    'transactions.invoice_no',
                    'products.product_description',
                    'transaction_details.quantity_sold',
                    'transaction_details.item_price',
                    'transaction_details.subtotal',
                    'transaction_details.discount',
                    'refunds.refunded_amount',
                    'tax_rates.tax_type',
                    'tax_rates.tax_rate',
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                    'transaction_details.is_refunded', // Add this line to select is_refunded
                ])
                ->orderBy('transactions.transaction_date', 'asc')
                ->orderBy('transactions.invoice_no', 'asc')
                ->get();

            // Format and organize the data as needed
            $formattedReport = $this->formatZAccumulatedSalesReport($zAccumulatedSalesReport);

            // Return the formatted Z Accumulated Sales Report as a response
            return response()->json(['data' => $formattedReport]);

        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Additional method to format the Z Accumulated Sales Report
    public function formatZAccumulatedSalesReport($zAccumulatedSalesReport)
    {
        // Initialize variables for total sales, total refunds, and tax totals
        $totalSales = 0;
        $totalRefunds = 0;
        $taxTotals = [];
        $refundTaxTotals = []; // Initialize refund tax totals

        // Iterate over the Z Accumulated Sales Report data and organize it
        foreach ($zAccumulatedSalesReport as $item) {
            $taxType = $item->tax_type;
            $taxRate = $item->tax_rate;
            $quantitySold = $item->quantity_sold ?? 0;
            $subtotal = $item->subtotal ?? 0;
            $refundedAmount = $item->refunded_amount ?? 0;
            $isRefunded = $item->is_refunded ?? 0;

            // Calculate total sales and refunds
            $totalSales += $subtotal;
            $totalRefunds += $refundedAmount;

            // Organize tax totals
            if (!isset($taxTotals[$taxType])) {
                $taxTotals[$taxType] = ['rate' => $taxRate, 'total' => 0];
            }
            $taxTotals[$taxType]['total'] += $subtotal;

            // Check if the product is refunded before including in refund tax totals
            if ($isRefunded) {
                // Organize refund tax totals
                if (!isset($refundTaxTotals[$taxType])) {
                    $refundTaxTotals[$taxType] = ['rate' => $taxRate, 'total' => 0];
                }
                $refundTaxTotals[$taxType]['total'] += $refundedAmount;
            }
        }

        // Generate the formatted Z Accumulated Sales Report
        $formattedZAccumulatedSalesReport = [
            'totals' => [
                'salesTotal' => $totalSales,
                'refundTotal' => $totalRefunds,
            ],
            'taxTotals' => $taxTotals,
            'refundTaxTotals' => $refundTaxTotals,
            // Add other necessary information based on your requirements
        ];

        return $formattedZAccumulatedSalesReport;
    }



    public function getSummaryFiscalBayDate(Request $request)
    {
        try {
            // Validate the incoming request data
            $this->validate($request, [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'location_id' => 'required|exists:locations,id',
            ]);

            // Retrieve the start_date, end_date, and location_id from the request
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $locationId = $request->input('location_id');

            // Fetch Summary Fiscal Bay Date Report using a query
            $summaryFiscalBayDate = DB::table('transactions')
                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->leftJoin('refunds', 'transactions.id', '=', 'refunds.transaction_id')
                ->leftJoin('tax_rates', 'products.tax_rate_id', '=', 'tax_rates.id')
                ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
                ->where('transactions.location_id', '=', $locationId)
                ->select([
                    'transactions.transaction_date',
                    DB::raw('SUM(transaction_details.quantity_sold) AS total_quantity_sold'),
                    DB::raw('SUM(transaction_details.subtotal) AS total_sales'),
                    DB::raw('SUM(transaction_details.discount) AS total_discount'),
                    DB::raw('SUM(refunds.refunded_amount) AS total_refunds'),
                    DB::raw('SUM(products.unit_cost * transaction_details.quantity_sold) AS total_cost'),
                    'tax_rates.tax_type',
                    'tax_rates.tax_rate',
                ])
                ->groupBy('transactions.transaction_date', 'tax_rates.tax_type', 'tax_rates.tax_rate')
                ->orderBy('transactions.transaction_date', 'asc')
                ->get();

            // Format and organize the data as needed
            $formattedReport = $this->formatSummaryFiscalBayDate($summaryFiscalBayDate);

            // Return the formatted Summary Fiscal Bay Date Report as a response
            return response()->json(['data' => $formattedReport]);

        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Additional method to format the Summary Fiscal Bay Date Report
    private function formatSummaryFiscalBayDate($summaryFiscalBayDate)
    {
        // Implement formatting logic based on your requirements
        // This could include formatting monetary values, adding labels, calculating taxes, etc.

        return $summaryFiscalBayDate;
    }

    public function getSummaryFiscalBayZ(Request $request)
    {
        try {
            // Validate the incoming request data
            $this->validate($request, [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'location_id' => 'required|exists:locations,id',
            ]);

            // Retrieve the start_date, end_date, and location_id from the request
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $locationId = $request->input('location_id');

            // Fetch Summary Fiscal Bay Z Report using a query
            $summaryFiscalBayZ = DB::table('transactions')
                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->leftJoin('refunds', 'transactions.id', '=', 'refunds.transaction_id')
                ->leftJoin('tax_rates', 'products.tax_rate_id', '=', 'tax_rates.id')
                ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
                ->where('transactions.location_id', '=', $locationId)
                ->select([
                    'transactions.transaction_date',
                    DB::raw('SUM(transaction_details.quantity_sold) AS total_quantity_sold'),
                    DB::raw('SUM(transaction_details.subtotal) AS total_sales'),
                    DB::raw('SUM(transaction_details.discount) AS total_discount'),
                    DB::raw('SUM(refunds.refunded_amount) AS total_refunds'),
                    DB::raw('SUM(products.unit_cost * transaction_details.quantity_sold) AS total_cost'),
                    'tax_rates.tax_type',
                    'tax_rates.tax_rate',
                ])
                ->groupBy('transactions.transaction_date', 'tax_rates.tax_type', 'tax_rates.tax_rate')
                ->orderBy('tax_rates.tax_type', 'asc')
                ->get();

            // Format and organize the data as needed
            $formattedReport = $this->formatSummaryFiscalBayZ($summaryFiscalBayZ);

            // Return the formatted Summary Fiscal Bay Z Report as a response
            return response()->json(['data' => $formattedReport]);

        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Additional method to format the Summary Fiscal Bay Z Report
    private function formatSummaryFiscalBayZ($summaryFiscalBayZ)
    {
        // Implement formatting logic based on your requirements
        // This could include formatting monetary values, adding labels, calculating taxes, etc.

        return $summaryFiscalBayZ;
    }

    public function getFullFiscalBayZ(Request $request)
    {
        try {
            // Validate the incoming request data
            $this->validate($request, [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'location_id' => 'required|exists:locations,id',
            ]);

            // Retrieve the start_date, end_date, and location_id from the request
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $locationId = $request->input('location_id');

            // Fetch Full Fiscal Bay Z Report using a query
            $fullFiscalBayZ = DB::table('transactions')
                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->leftJoin('refunds', 'transactions.id', '=', 'refunds.transaction_id')
                ->leftJoin('tax_rates', 'products.tax_rate_id', '=', 'tax_rates.id')
                ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
                ->where('transactions.location_id', '=', $locationId)
                ->select([
                    'transactions.transaction_date',
                    'transactions.invoice_no',
                    'products.product_description',
                    'transaction_details.quantity_sold',
                    'transaction_details.item_price',
                    'transaction_details.subtotal',
                    'transaction_details.discount',
                    'refunds.refunded_amount',
                    'products.unit_cost',
                    'tax_rates.tax_type',
                    'tax_rates.tax_rate',
                ])
                ->orderBy('transactions.transaction_date', 'asc')
                ->orderBy('transactions.invoice_no', 'asc')
                ->orderBy('products.product_description', 'asc')
                ->get();

            // Format and organize the data as needed
            $formattedReport = $this->formatFullFiscalBayZ($fullFiscalBayZ);

            // Return the formatted Full Fiscal Bay Z Report as a response
            return response()->json(['data' => $formattedReport]);

        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Additional method to format the Full Fiscal Bay Z Report
    private function formatFullFiscalBayZ($fullFiscalBayZ)
    {
        // Implement formatting logic based on your requirements
        // This could include formatting monetary values, adding labels, calculating taxes, etc.

        return $fullFiscalBayZ;
    }

    public function getFullFiscalByDate(Request $request)
    {
        try {
            // Validate the incoming request data
            $this->validate($request, [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'location_id' => 'required|exists:locations,id',
            ]);

            // Retrieve the start_date, end_date, and location_id from the request
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $locationId = $request->input('location_id');

            // Fetch Full Fiscal By Date Report using a query
            $fullFiscalByDate = DB::table('transactions')
                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->leftJoin('refunds', 'transactions.id', '=', 'refunds.transaction_id')
                ->leftJoin('tax_rates', 'products.tax_rate_id', '=', 'tax_rates.id')
                ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
                ->where('transactions.location_id', '=', $locationId)
                ->select([
                    'transactions.transaction_date',
                    'transactions.invoice_no',
                    'products.product_description',
                    'transaction_details.quantity_sold',
                    'transaction_details.item_price',
                    'transaction_details.subtotal',
                    'transaction_details.discount',
                    'refunds.refunded_amount',
                    'products.unit_cost',
                    'tax_rates.tax_type',
                    'tax_rates.tax_rate',
                ])
                ->orderBy('transactions.transaction_date', 'asc')
                ->orderBy('transactions.invoice_no', 'asc')
                ->orderBy('products.product_description', 'asc')
                ->get();

            // Format and organize the data as needed
            $formattedReport = $this->formatFullFiscalByDate($fullFiscalByDate);

            // Return the formatted Full Fiscal By Date Report as a response
            return response()->json(['data' => $formattedReport]);

        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Additional method to format the Full Fiscal By Date Report
    private function formatFullFiscalByDate($fullFiscalByDate)
    {
        // Implement formatting logic based on your requirements
        // This could include formatting monetary values, adding labels, calculating taxes, etc.

        return $fullFiscalByDate;
    }

}
