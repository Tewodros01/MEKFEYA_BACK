<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserRegisterController;
use App\Http\Controllers\User\UserLoginController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Settings\ProductCategoryController;
use App\Http\Controllers\Settings\TaxRateController;
use App\Http\Controllers\Settings\LocationController;
use App\Http\Controllers\Settings\UnitOfMeasurementController;
use App\Http\Controllers\RolePermission\RoleController;
use App\Http\Controllers\RolePermission\PermissionController;
use App\Http\Controllers\RolePermission\RolePermissionController;
use App\Http\Controllers\RolePermission\RoleLocationPermissionController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Organization\OrganizationController;
use App\Http\Controllers\Transaction\TransactionController;
use App\Http\Controllers\Transaction\TransactionReportController;
use App\Http\Controllers\Transaction\SalesReportController;
use App\Http\Controllers\FiscalReading\FisclaUpload;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Transaction\RefundController;
use App\Http\Controllers\ThermalPrinters\ReceiptThermalPrintersController;
use GuzzleHttp\Promise\Create;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:api')->get('/users', function (Request $request) {
    return $request->user();
});

Route::group(
    [
        'prefix' => 'settings',
        //'middleware' => 'auth:api',
    ],
    function () {
        /* product category setting  end points  */
        Route::post('postProductCategoty', [ProductCategoryController::class, 'create']);
        Route::get('getAllProductCategoty', [ProductCategoryController::class, 'index']);
        Route::get('getProductCategoty/{id}', [ProductCategoryController::class, 'show']);
        Route::put('updateProductCategoty/{id}', [ProductCategoryController::class, 'update']);
        Route::delete('deleteProductCategoty', [ProductCategoryController::class, 'delete']);
        /* location setting  end points  */
        Route::post('postLocation', [LocationController::class, 'create']);
        Route::get('getAllLocation', [LocationController::class, 'index']);
        Route::put('updateLocation', [LocationController::class, 'update']);
        Route::delete('deleteLocation', [LocationController::class, 'delete']);
        Route::get('getLocationById', [LocationController::class, 'show']);
        /* customer setting end points  */
        Route::post('postCustomer', [CustomerController::class, 'create']);
        Route::get('getAllCustomers', [CustomerController::class, 'index']);
        Route::get('getCustomer', [CustomerController::class, 'show']);
        Route::put('updateCustomer', [CustomerController::class, 'update']);
        Route::delete('deleteCustomer', [CustomerController::class, 'delete']);
        /* organization settign end points */
        Route::post('postOrganization', [OrganizationController::class, 'create']);
        Route::get('getOrganization/{id}', [OrganizationController::class, 'show']);
        Route::get('getAllOrganization', [OrganizationController::class, 'index']);
        Route::put('updateOrganization/{id}', [OrganizationController::class, 'update']);
        Route::delete('deleteOrganization/{id}', [OrganizationController::class, 'delete']);
        /* taxrate setting end points  */
        Route::post('postTaxRate', [TaxRateController::class, 'create']);
        Route::get('getAllTaxRate', [TaxRateController::class, 'index']);
        Route::get('getTaxRate/{id}', [TaxRateController::class, 'show']);
        Route::put('updateTaxRate/{id}', [TaxRateController::class, 'update']);
        /* Unit Of Measurement  setting end points  */
        Route::get('getAllUnits', [UnitOfMeasurementController::class, 'index']);
        Route::post('postUnit', [UnitOfMeasurementController::class, 'store']);
        Route::get('getUnit/{id}', [UnitOfMeasurementController::class, 'show']);
        Route::put('updateUnits/{id}', [UnitOfMeasurementController::class, 'update']);
        Route::delete('deleteUnits/{id}', [UnitOfMeasurementController::class, 'destroy']);
    }
);

Route::group(
    [
        'prefix' => 'auth',
        // 'middleware'=>'auth:api',
    ],
    function () {
        Route::post('changePassword', [UserController::class, 'changePassword']);
        Route::post('forgotPassword', [UserController::class, 'forgotPassword']);
        Route::post('resetPassword', [UserController::class, 'resetPassword']);
    }
);


Route::post('login', [UserLoginController::class, 'loginUser']);

Route::group(
    [
        'prefix' => 'user',
        //'middleware' => 'auth:api',
    ],
    function () {
        /* use accessing ind ponts  */
        Route::post('register', [UserRegisterController::class, 'register']);
        Route::get('getAllUser', [UserController::class, 'index']);
        Route::get('getUse/{id}r', [UserController::class, 'show']);
        Route::post('updateUser', [UserController::class, 'updateUser']);
        Route::delete('deleteUser/{id}', [UserController::class, 'deleteUser']);
        Route::put('inactiveUser/{id}', [UserController::class, 'inactiveUser']);
        Route::put('activateUser/{id}', [UserController::class, 'activeUser']);

        Route::get('getAllUserGroupedByLocation', [UserController::class, 'indexGroupWithLocation']);
        Route::get('getAllUserGroupedByRole', [UserController::class, 'indexGroupWithRole']);
    }
);

Route::group(
    [
        'prefix' => 'role',
        //'middleware' => 'auth:api',
    ],
    function () {
        /* role setting end poins */
        Route::post('postRole', [RoleController::class, 'create']);
        Route::get('getAllRole', [RoleController::class, 'index']);
        Route::get('getAllRoleCount', [RoleController::class, 'indexRoles']);
        Route::put('updateRole/{id}', [RoleController::class, 'update']);
        Route::delete('deleteRole/{id}', [RoleController::class, 'delete']);
        Route::get('getRole/{id}', [RoleController::class, 'show']);
        Route::get('getRoleWithPermission', [RoleController::class, 'indexWitPermission']);
        Route::post('postPermission', [PermissionController::class, 'create']);
        Route::get('getAllPermission', [PermissionController::class, 'index']);
        Route::put('updatePermission', [PermissionController::class, 'update']);
        Route::delete('deletePermission', [PermissionController::class, 'delete']);
        Route::get('getPermissionById', [PermissionController::class, 'show']);
        /* role permissiont setting end points  */
        Route::post('postRolePermission', [RolePermissionController::class, 'create']);
        Route::get('getAllRolePermission', [RolePermissionController::class, 'index']);
        Route::put('updateRolePermission/{id}', [RolePermissionController::class, 'update']);
        Route::delete('deleteRolePermission/{id}', [RolePermissionController::class, 'delete']);
        Route::delete('deleteRolePermissionByParames', [RolePermissionController::class, 'destroyByParams']);
        Route::post('postRolePermissionsByBatch', [RolePermissionController::class, 'postRolePermissionsBatch']);
        Route::post('deleteRolePermissionsByBatch', [RolePermissionController::class, 'deleteRolePermissionsBatch']);
        Route::get('getRolePermission/{id}', [RolePermissionController::class, 'show']);
        Route::get('getAllRoleLocationPermissions', [RoleLocationPermissionController::class, 'index']);
        Route::get('getRoleLocationPermissions/{id}', [RoleLocationPermissionController::class, 'show']);
        Route::post('postRoleLocationPermissions', [RoleLocationPermissionController::class, 'store']);
        Route::put('updateRoleLocationPermissions/{id}', [RoleLocationPermissionController::class, 'update']);
        Route::delete('deleteRoleLocationPermissions/{id}', [RoleLocationPermissionController::class, 'destroy']);
        Route::delete('deleteRoleLocationPermissionsByParames', [RoleLocationPermissionController::class, 'destroyByParams']);
        Route::post('postRoleLocationPermissionsByBatch', [RoleLocationPermissionController::class, 'postRoleLocationPermissionsBatch']);
        Route::post('deleteRoleLocationPermissionsByBatch', [RoleLocationPermissionController::class, 'deleteRoleLocationPermissionsBatch']);
    }

);

Route::group(
    [
        'prefix' => 'product',
        //'middleware' =>'auth:api',

    ],
    function () {
        Route::post('postItem', [ProductController::class, 'create']);
        Route::get('getAllItems', [ProductController::class, 'index']);
        Route::get('getAvailableProducts', [ProductController::class, 'getAvailableProducts']);
        Route::get('getAllItemsCount', [ProductController::class, 'productIndex']);
        Route::get('getItem/{id}', [ProductController::class, 'show']);
        Route::post('update/{id}', [ProductController::class, 'update']);
        Route::delete('deleteItems/{id}', [ProductController::class, 'delete']);
        Route::get('getItemsByLocation/{location}', [ProductController::class, 'getByLocation']);
        Route::get('getItemsExpired', [ProductController::class, 'getExpiredProducts']);
        Route::get('getItemsExpiringThreeMonth', [ProductController::class, 'getExpiringProductsWithThreeMonth']);
        Route::get('getItemsExpiringSixMonth', [ProductController::class, 'getProductsExpiringIn3to6Months']);
    }
);

Route::group(
    [
        'prefix' => 'fiscal',
        //'middleware' => 'auth:api',
    ],
    function () {
        Route::post('uploadEJ', [FisclaUpload::class, 'upload']);
        Route::get('/print-receipt', [ReceiptThermalPrintersController::class, 'printReceipt']);
    }
);

Route::group(
    [
        'prefix' => 'transaction',
        //'middleware' =>'auth:api',
    ],
    function () {
        Route::post('postTransaction', [TransactionController::class, 'create']);
        Route::get('getAllTransactions', [TransactionController::class, 'index']);
        Route::get('getTransaction/{id}', [TransactionController::class, 'show']);
        Route::get('getDailyTransaction', [TransactionController::class, 'getDailyTransactions']);
        Route::post('voidTransaction/{id}', [TransactionController::class, 'voidTransaction']);

        Route::post('showDailySalsdProductByDateUserIDAndLocationId', [TransactionController::class, 'showDailySalsdProductByDateUserIDAndLocationId']);
        Route::post('showDailySalsProductByDate', [TransactionController::class, 'showDailySalsProductByDate']);


        /* refunds end points */
        Route::post('postRefund', [RefundController::class, 'create']);
        Route::get('getAllRefunds', [RefundController::class, 'index']);
        Route::get('getRefund/{id}', [RefundController::class, 'show']);
        Route::get('getRefundByRefNo/{ref_no}', [RefundController::class, 'showByRfNo']);

    }
);

Route::group(
    [
        'prefix' => 'report',
        //'middleware' =>'auth:api',
    ],
    function () {
        Route::post('cashSalesReports/{salesType}', [TransactionReportController::class, 'getTransactionsBySalesType']);
        Route::post('creditSalesReports/{salesType}', [TransactionReportController::class, 'getTransactionsBySalesType']);
        Route::get('expired', [ProductController::class, 'getExpiredProducts']);
        Route::get('getExpiringReport', [ProductController::class, 'getExpiredAndExpiringProducts']);
        Route::get('getProductSales', [TransactionReportController::class, 'getProductSales']);
        Route::get('getOverallProductSales', [TransactionReportController::class, 'getOverallProductSales']);
        Route::get('getTopProducts', [TransactionReportController::class, 'getTopProducts']);
        Route::get('getProductsRankedByEarnings', [TransactionReportController::class, 'getProductsRankedByEarnings']);
        Route::get('getProductsRankedByDemand', [TransactionReportController::class, 'getProductsRankedByDemand']);
        Route::get('getWeeklyTransactions', [TransactionReportController::class, 'getWeeklyTransactions']);
        Route::get('getTotalProfitsAndExpenses', [TransactionReportController::class, 'getTotalProfitsAndExpenses']);
        Route::get('getYearlySalesWithMonthlyBreakdown', [TransactionReportController::class, 'getYearlySalesWithMonthlyBreakdown']);
        Route::get('getFinancialSummary', [TransactionReportController::class, 'getFinancialSummary']);
        Route::get('getAllSalesData', [TransactionReportController::class, 'getSalesData']);
    }
);

Route::group(
    [
        'prefix' => 'salesreport',
        //'middleware' =>'auth:api',
    ],
    function () {
        Route::post('getDailyXReport', [SalesReportController::class, 'getXDailySalesReport']);
        Route::post('getDailyZReport', [SalesReportController::class, 'getZDailySalesReport']);
        Route::post('getXAccumulatedSalesReport', [SalesReportController::class, 'getXAccumulatedSalesReport']);
        Route::post('getZAccumulatedSalesReport', [SalesReportController::class, 'getZAccumulatedSalesReport']);
        Route::post('getSummaryFiscalBayDate', [SalesReportController::class, 'getSummaryFiscalBayDate']);
        Route::post('getSummaryFiscalBayZ', [SalesReportController::class, 'getSummaryFiscalBayZ']);
        Route::post('getFullFiscalBayZ', [SalesReportController::class, 'getFullFiscalBayZ']);
        Route::post('getFullFiscalByDate', [SalesReportController::class, 'getFullFiscalByDate']);
   }
);
