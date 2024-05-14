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

class TransactionReportController extends Controller
{

    public function getTransactionsBySalesType(Request $request, $salesType)
    {
        // Check if date range parameters are provided
        if ($request->has(['TransactionFromdate', 'TransactionTodate'])) {
            // Validate the optional date range
            $request->validate([
                'TransactionFromdate' => 'required|date_format:Y-m-d',
                'TransactionTodate' => 'required|date_format:Y-m-d',
            ]);

            // Extract dates from the request
            $fromDate = $request->input('TransactionFromdate');
            $toDate = $request->input('TransactionTodate');

            // Ensure $fromDate is earlier than $toDate
            if (Carbon::parse($fromDate)->gt(Carbon::parse($toDate))) {
                return response()->json(['error' => 'Invalid date range'], 400);
            }

            // Start building the query with date range
            $query = TransactionModel::with(['salesBy', 'customer', 'payment', 'location'])
                ->where('sales_type', $salesType)
                ->whereBetween('transaction_date', [$fromDate, $toDate]);
        } else {
            // Start building the query without date range
            $query = TransactionModel::with(['salesBy', 'customer', 'payment', 'location'])
                ->where('sales_type', $salesType);
        }

        // Fetch transactions
        $transactions = $query->get();

        return response()->json($transactions);
    }

    public function getProductsRankedByEarnings()
    {
        try {
            $resultHighest = DB::table('products as p')
                ->join('transaction_details as td', 'p.id', '=', 'td.product_id')
                ->join('transactions as t', 'td.transaction_id', '=', 't.id')
                ->select(
                    'p.id as product_id',
                    'p.product_description',
                    'p.product_code',
                    DB::raw('SUM(td.subtotal - (p.unit_cost * td.quantity_sold)) as total_earnings')
                )
                ->where('t.is_void', 0)
                ->groupBy('p.id', 'p.product_description', 'p.product_code')
                ->orderByDesc('total_earnings')
                ->limit(5)
                ->get();

            $resultLowest = DB::table('products as p')
                ->join('transaction_details as td', 'p.id', '=', 'td.product_id')
                ->join('transactions as t', 'td.transaction_id', '=', 't.id')
                ->select(
                    'p.id as product_id',
                    'p.product_description',
                    'p.product_code',
                    DB::raw('SUM(td.subtotal - (p.unit_cost * td.quantity_sold)) as total_earnings')
                )
                ->where('t.is_void', 0)
                ->groupBy('p.id', 'p.product_description', 'p.product_code')
                ->orderBy('total_earnings')
                ->limit(5)
                ->get();

            return response()->json([
                'highest_earnings' => $resultHighest,
                'lowest_earnings' => $resultLowest,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getProductsRankedByDemand()
    {
        try {
            $resultHighest = DB::table('products as p')
                ->join('transaction_details as td', 'p.id', '=', 'td.product_id')
                ->join('transactions as t', 'td.transaction_id', '=', 't.id')
                ->select(
                    'p.id as product_id',
                    'p.product_description',
                    'p.product_code',
                    DB::raw('SUM(td.quantity_sold) as total_demand')
                )
                ->where('t.is_void', 0)
                ->groupBy('p.id', 'p.product_description', 'p.product_code')
                ->orderByDesc('total_demand')
                ->limit(5)
                ->get();

            $resultLowest = DB::table('products as p')
                ->join('transaction_details as td', 'p.id', '=', 'td.product_id')
                ->join('transactions as t', 'td.transaction_id', '=', 't.id')
                ->select(
                    'p.id as product_id',
                    'p.product_description',
                    'p.product_code',
                    DB::raw('SUM(td.quantity_sold) as total_demand')
                )
                ->where('t.is_void', 0)
                ->groupBy('p.id', 'p.product_description', 'p.product_code')
                ->orderBy('total_demand')
                ->limit(5)
                ->get();

            return response()->json([
                'highest_demand' => $resultHighest,
                'lowest_demand' => $resultLowest,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getTopProducts()
    {
        try {
            $result = DB::table('products as p')
                ->join('transaction_details as td', 'p.id', '=', 'td.product_id')
                ->join('transactions as t', 'td.transaction_id', '=', 't.id')
                ->select(
                    'p.id as product_id',
                    'p.product_description',
                    'p.product_code',
                    DB::raw('SUM(td.quantity_sold) as total_quantity_sold'),
                    DB::raw('SUM(td.subtotal) as total_sales'),
                    DB::raw('SUM(td.subtotal - (p.unit_cost * td.quantity_sold)) as total_profit')
                )
                ->where('t.is_void', 0)
                ->groupBy('p.id', 'p.product_description', 'p.product_code')
                ->orderByDesc('total_quantity_sold')
                ->orderByDesc('total_profit')
                ->limit(5)
                ->get();

            return response($result, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getOverallProductSales()
    {
        try {
            $result = DB::table('products as p')
                ->join('transaction_details as td', 'p.id', '=', 'td.product_id')
                ->join('transactions as t', 'td.transaction_id', '=', 't.id')
                ->select(
                    DB::raw('SUM(td.quantity_sold) as total_quantity_sold'),
                    DB::raw('SUM(td.subtotal) as total_sales'),
                    DB::raw('SUM(td.subtotal - (p.unit_cost * td.quantity_sold)) as total_profit')
                )
                ->where('t.is_void', 0)
                ->first();

            return response()->json([
                'totalProfit' => $result->total_profit ?? 0,
                'totalProduct' => $result->total_quantity_sold ?? 0,
                'totalSales' => $result->total_sales ?? 0,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getWeeklyTransactions()
    {
        try {
            Carbon::setWeekStartsAt(Carbon::MONDAY);
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();

            // Initialize daily totals array
            $dailyTotals = [
                'Monday' => ['totalProfit' => 0, 'totalProduct' => 0, 'totalSales' => 0],
                'Tuesday' => ['totalProfit' => 0, 'totalProduct' => 0, 'totalSales' => 0],
                'Wednesday' => ['totalProfit' => 0, 'totalProduct' => 0, 'totalSales' => 0],
                'Thursday' => ['totalProfit' => 0, 'totalProduct' => 0, 'totalSales' => 0],
                'Friday' => ['totalProfit' => 0, 'totalProduct' => 0, 'totalSales' => 0],
                'Saturday' => ['totalProfit' => 0, 'totalProduct' => 0, 'totalSales' => 0],
                'Sunday' => ['totalProfit' => 0, 'totalProduct' => 0, 'totalSales' => 0],
            ];

            $transactions = DB::table('transactions')
                ->select('id', 'transaction_date', 'total_amount','total_vat')
                ->whereBetween('transaction_date', [$startOfWeek, $endOfWeek])
                ->get();

            // Calculate daily and weekly totals
            foreach ($transactions as $transaction) {
                $dayOfWeek = Carbon::parse($transaction->transaction_date)->dayName;

                // Increment daily totals
                $dailyTotals[$dayOfWeek]['totalProfit'] += $transaction->total_amount - $transaction->total_vat - $this->calculateCostOfGoodsSold($transaction->id);
                $dailyTotals[$dayOfWeek]['totalProduct'] += DB::table('transaction_details')
                    ->where('transaction_id', $transaction->id)
                    ->sum('quantity_sold');
                $dailyTotals[$dayOfWeek]['totalSales'] += $transaction->total_amount;
            }

            // Calculate weekly totals
            $weeklyTotals = [
                'totalProfit' => collect($dailyTotals)->sum('totalProfit'),
                'totalProduct' => collect($dailyTotals)->sum('totalProduct'),
                'totalSales' => collect($dailyTotals)->sum('totalSales'),
            ];

            return response()->json([
                'daily_totals' => $dailyTotals,
                'weekly_totals' => $weeklyTotals,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error_message' => $e->getMessage()], 500);
        }
    }

    // Helper function to calculate cost of goods sold for a transaction
    private function calculateCostOfGoodsSold($transactionId)
    {
        return DB::table('transaction_details')
            ->leftJoin('products', 'transaction_details.product_id', '=', 'products.id')
            ->select('quantity_sold','unit_cost')
            ->where('transaction_id', $transactionId)
            ->sum(DB::raw('quantity_sold * unit_cost'));
    }

    public function getProductSales()
    {
        try {
            $result = DB::table('products as p')
                ->join('transaction_details as td', 'p.id', '=', 'td.product_id')
                ->join('transactions as t', 'td.transaction_id', '=', 't.id')
                ->select(
                    'p.id as product_id',
                    'p.product_description',
                    'p.product_code',
                    DB::raw('SUM(td.quantity_sold) as total_quantity_sold'),
                    DB::raw('SUM(td.subtotal) as total_sales'),
                    DB::raw('SUM(td.subtotal - (p.unit_cost * td.quantity_sold)) as total_profit')
                )
                ->where('t.is_void', 0)
                ->groupBy('p.id', 'p.product_description', 'p.product_code')
                ->get();

            return response()->json(['data' => $result], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getDailyTransactions()
    {
        try {
            $now = Carbon::now()->toDateString();
            $today = Carbon::createFromFormat('Y-m-d', $now, 'UTC')->setTimezone('Africa/Addis_Ababa')->toDateString();
            // return $today;
            $transaction = TransactionModel::with(['salesBy', 'transactionDetails.product.taxRate', 'customer', 'payment', 'location'])
                ->where('transaction_date', $today)
                ->get();

            return response($transaction, 200);
        } catch (\Exception $e) {
            return response()->json(['error_message' => $e->getMessage()], 500);
        }
    }

    public function getTotalProfitsAndExpenses()
    {
        try {
            // Calculate Total Gross Profit
            $totalGrossProfit = DB::table('transaction_details as td')
                ->join('transactions as t', 'td.transaction_id', '=', 't.id')
                ->join('products as p', 'td.product_id', '=', 'p.id')
                ->select(DB::raw('SUM(td.subtotal - (p.unit_cost * td.quantity_sold)) as total_gross_profit'))
                ->where('t.is_void', 0)
                ->value('total_gross_profit');

            // Calculate Total Net Profit
            $totalNetProfit = DB::table('transaction_details as td')
                ->join('transactions as t', 'td.transaction_id', '=', 't.id')
                ->join('products as p', 'td.product_id', '=', 'p.id')
                ->leftJoin('tax_rates as tr', 'p.tax_rate_id', '=', 'tr.id')
                ->select(DB::raw('SUM((td.subtotal - (p.unit_cost * td.quantity_sold)) - td.discount - (td.subtotal * tr.tax_rate / 100)) as total_net_profit'))
                ->where('t.is_void', 0)
                ->value('total_net_profit');

            // Calculate Total Expense
            $totalExpense = DB::table('transactions as t')
                ->select(DB::raw('SUM(t.total_discount + t.total_vat) as total_expense'))
                ->where('t.is_void', 0)
                ->value('total_expense');

            return response()->json([
                'totalGrossProfit' => $totalGrossProfit ?? 0,
                'totalNetProfit' => $totalNetProfit ?? 0,
                'totalExpense' => $totalExpense ?? 0,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getYearlySalesWithMonthlyBreakdown()
    {
        try {
            $year = now()->year;

            $monthlySales = DB::table('transactions as t')
                ->select(
                    DB::raw('SUM(t.total_amount) as monthly_sales'),
                    DB::raw('MONTH(t.transaction_date) as month_number')
                )
                ->whereYear('t.transaction_date', $year)
                ->groupBy(DB::raw('MONTH(t.transaction_date)'))
                ->get();

            $monthlySalesData = [];
            $months = [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];

            foreach ($months as $index => $month) {
                $monthlySalesData[$month] = 0;
                foreach ($monthlySales as $monthlySale) {
                    if ($monthlySale->month_number == ($index + 1)) {
                        $monthlySalesData[$month] = $monthlySale->monthly_sales;
                        break;
                    }
                }
            }

            return response()->json(['year' => $year, 'monthly_sales' => $monthlySalesData], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getSalesData()
    {
        // Fetch sales data with product information and location
        $salesData = DB::table('transaction_details')
            ->select(
                'transactions.transaction_date',
                'transactions.invoice_no',
                'products.product_description',
                'products.product_code',
                'transaction_details.quantity_sold',
                'transaction_details.item_price',
                'transaction_details.subtotal',
                'locations.location_name'
            )
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('locations', 'transactions.location_id', '=', 'locations.id')
            ->get();

        return response()->json(['salesData' => $salesData]);
    }

    public function getFinancialSummary()
    {
        try {
            $financialSummary = [
                'totalGrossProfit' => $this->calculateTotalGrossProfit(),
                'grosProfitWithTimeRange' => [
                    'gros_profit_month_to_date' => $this->calculateGrossProfitForTimeRange('month_to_date'),
                    'gros_profit_quarter_to_date' => $this->calculateGrossProfitForTimeRange('quarter_to_date'),
                    'gros_profit_year_to_date' => $this->calculateGrossProfitForTimeRange('year_to_date'),
                    'gros_profit_last_month' => $this->calculateGrossProfitForTimeRange('last_month'),
                    'gros_profit_last_quarter' => $this->calculateGrossProfitForTimeRange('last_quarter'),
                    'gros_profit_last_year' => $this->calculateGrossProfitForTimeRange('last_year'),
                ],
                'totalNetProfit' => $this->calculateTotalNetProfit(),
                'netProfitWithTimeRange' => [
                    'net_profit_month_to_date' => $this->calculateNetProfitForTimeRange('month_to_date'),
                    'net_profit_quarter_to_date' => $this->calculateNetProfitForTimeRange('quarter_to_date'),
                    'net_profit_year_to_date' => $this->calculateNetProfitForTimeRange('year_to_date'),
                    'net_profit_last_month' => $this->calculateNetProfitForTimeRange('last_month'),
                    'net_profit_last_quarter' => $this->calculateNetProfitForTimeRange('last_quarter'),
                    'net_profit_last_year' => $this->calculateNetProfitForTimeRange('last_year'),
                ],
                'totalExpenses' => $this->calculateTotalExpenses(),
                'expensesWithTimeRange' => [
                    'expenses_month_to_date' => $this->calculateExpensesForTimeRange('month_to_date'),
                    'expenses_quarter_to_date' => $this->calculateExpensesForTimeRange('quarter_to_date'),
                    'expenses_year_to_date' => $this->calculateExpensesForTimeRange('year_to_date'),
                    'expenses_last_month' => $this->calculateExpensesForTimeRange('last_month'),
                    'expenses_last_quarter' => $this->calculateExpensesForTimeRange('last_quarter'),
                    'expenses_last_year' => $this->calculateExpensesForTimeRange('last_year'),
                ],
            ];

            return response()->json($financialSummary, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function calculateGrossProfitForTimeRange($timeRange)
    {
        $dateRange = $this->getDateRange($timeRange);

        return DB::table('transaction_details as td')
            ->join('transactions as t', 'td.transaction_id', '=', 't.id')
            ->join('products as p', 'td.product_id', '=', 'p.id')
            ->where('t.is_void', 0)
            ->whereBetween('t.transaction_date', $dateRange)
            ->sum(DB::raw('(td.subtotal - (p.unit_cost * td.quantity_sold))'));
    }

    private function calculateNetProfitForTimeRange($timeRange)
    {
        $dateRange = $this->getDateRange($timeRange);

        return DB::table('transaction_details as td')
            ->join('transactions as t', 'td.transaction_id', '=', 't.id')
            ->join('products as p', 'td.product_id', '=', 'p.id')
            ->leftJoin('tax_rates as tr', 'p.tax_rate_id', '=', 'tr.id')
            ->where('t.is_void', 0)
            ->whereBetween('t.transaction_date', $dateRange)
            ->sum(DB::raw('(td.subtotal - (p.unit_cost * td.quantity_sold)) - td.discount - (td.subtotal * tr.tax_rate / 100)'));
    }

    private function calculateExpensesForTimeRange($timeRange)
    {
        $dateRange = $this->getDateRange($timeRange);

        return DB::table('transactions as t')
            ->where('t.is_void', 0)
            ->whereBetween('t.transaction_date', $dateRange)
            ->sum(DB::raw('t.total_discount + t.total_vat'));
    }

    private function calculateTotalGrossProfit()
    {
        return DB::table('transaction_details as td')
            ->join('transactions as t', 'td.transaction_id', '=', 't.id')
            ->join('products as p', 'td.product_id', '=', 'p.id')
            ->where('t.is_void', 0)
            ->sum(DB::raw('td.subtotal - (p.unit_cost * td.quantity_sold)'));
    }

    private function calculateTotalNetProfit()
    {
        return DB::table('transaction_details as td')
            ->join('transactions as t', 'td.transaction_id', '=', 't.id')
            ->join('products as p', 'td.product_id', '=', 'p.id')
            ->leftJoin('tax_rates as tr', 'p.tax_rate_id', '=', 'tr.id')
            ->where('t.is_void', 0)
            ->sum(DB::raw('(td.subtotal - (p.unit_cost * td.quantity_sold)) - td.discount - (td.subtotal * tr.tax_rate / 100)'));
    }

    private function calculateTotalExpenses()
    {
        return DB::table('transactions as t')
            ->where('t.is_void', 0)
            ->sum(DB::raw('t.total_discount + t.total_vat'));
    }
    private function getDateRange($timeRange)
    {
        $now = Carbon::now();

        switch ($timeRange) {
            case 'month_to_date':
                $dateRange = [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
                break;
            case 'quarter_to_date':
                $dateRange = [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()];
                break;
            case 'year_to_date':
                $dateRange = [$now->copy()->startOfYear(), $now->copy()->endOfYear()];
                break;
            case 'last_month':
                $lastMonth = $now->copy()->subMonth();
                $dateRange = [$lastMonth->copy()->startOfMonth(), $lastMonth->copy()->endOfMonth()];
                break;
            case 'last_quarter':
                $lastQuarter = $now->copy()->subQuarter();
                $dateRange = [$lastQuarter->copy()->startOfQuarter(), $lastQuarter->copy()->endOfQuarter()];
                break;
            case 'last_year':
                $lastYear = $now->copy()->subYear();
                $dateRange = [$lastYear->copy()->startOfYear(), $lastYear->copy()->endOfYear()];
                break;
            default:
                $dateRange = [];
        }

        return $dateRange;
    }

}
