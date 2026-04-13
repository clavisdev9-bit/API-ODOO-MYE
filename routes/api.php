<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OdooController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\CourierPriceListController;
use App\Http\Controllers\Api\CustomersController;
use App\Http\Controllers\Api\DcController;
use App\Http\Controllers\Api\InventoryBalanceController;
use App\Http\Controllers\Api\InsuranceReportController;
use App\Http\Controllers\Api\StatutorySalesReportController;
use App\Http\Controllers\Api\PodHandOverController;
use App\Http\Controllers\Api\OutstandingGrController;
use App\Http\Controllers\Api\OutstandingDispatchController;
use App\Http\Controllers\Api\OutstandingDispatchWithFreightCostController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// GET http://localhost:8000/api/odoo/try-db
Route::get('/odoo/try-db', function () {
    $databases = [
        'edu-wpc',
        'edu-student4',
        'edu_student4',
        'eduwpc',
    ];

    $results = [];

    foreach ($databases as $db) {
        $xml = '<?xml version="1.0"?>
        <methodCall>
            <methodName>authenticate</methodName>
            <params>
                <param><value><string>' . $db . '</string></value></param>
                <param><value><string>aristya.r@outlook.com</string></value></param>
                <param><value><string>P@ssw0rdAr123</string></value></param>
                <param><value><array><data></data></array></value></param>
            </params>
        </methodCall>';

        $ch = curl_init('https://edu-wpc.odoo.com/xmlrpc/2/common');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);

        $response = curl_exec($ch);
        curl_close($ch);

        // Cek apakah dapat UID (angka) atau false
        preg_match('/<int>(\d+)<\/int>/', $response, $match);
        preg_match('/<boolean>(\d)<\/boolean>/', $response, $boolMatch);

        $results[$db] = [
            'uid'    => $match[1]  ?? null,
            'result' => $boolMatch[1] ?? null,
            'raw'    => $response,
        ];
    }

    return response()->json($results);
});


// GET http://localhost:8000/api/odoo/debug-auth
Route::get('/odoo/debug-auth', function () {
    $databases = ['edu-wpc', 'edu-student4', 'edu_wpc', 'eduwpc'];
    $passwords = ['1111', 'P@ssw0rdAr123'];
    $user = 'aristya.r@outlook.com';
    
    $results = [];

    foreach ($databases as $db) {
        foreach ($passwords as $pass) {
            $xml = '<?xml version="1.0"?>
            <methodCall>
                <methodName>authenticate</methodName>
                <params>
                    <param><value><string>' . $db . '</string></value></param>
                    <param><value><string>' . $user . '</string></value></param>
                    <param><value><string>' . $pass . '</string></value></param>
                    <param><value><array><data></data></array></value></param>
                </params>
            </methodCall>';

            $ch = curl_init('https://edu-wpc.odoo.com/xmlrpc/2/common');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
            $response = curl_exec($ch);
            curl_close($ch);

            preg_match('/<int>(\d+)<\/int>/', $response, $intMatch);
            preg_match('/<boolean>(\d)<\/boolean>/', $response, $boolMatch);

            $results["{$db} | pass:{$pass}"] = [
                'uid'     => $intMatch[1]  ?? null,
                'boolean' => $boolMatch[1] ?? null,
                'raw'     => $response,
            ];
        }
    }

    return response()->json($results, 200, [], JSON_PRETTY_PRINT);
});



// Routes Test Connection API untuk Odoo
Route::prefix('odoo')->group(function () {
    // Test koneksi
    Route::get('/ping', [OdooController::class, 'ping']);
    // Generic query (akses model Odoo apa saja)
    Route::post('/query', [OdooController::class, 'query']);
    // Students / Partners

    Route::get('/students',          [StudentController::class, 'students']);
    Route::get('/students/{id}',     [StudentController::class, 'showStudent']);
    Route::post('/students',         [StudentController::class, 'storeStudent']);
    Route::put('/students/{id}',     [StudentController::class, 'updateStudent']);
    Route::delete('/students/{id}',  [StudentController::class, 'destroyStudent']);
 
});



// Routes untuk Courier Price Lists
Route::prefix('odoo')->group(function () {
    // Customers
    Route::get('/customers',       [CustomersController::class, 'index']);
    Route::get('/customers/{id}',  [CustomersController::class, 'show']);
      // filter  customer  by DC (opsional, alternatif query param)
    Route::get('customers/{id}/dc',      [DCController::class, 'byCustomer']);

    // DC
    Route::get('dc',      [DcController::class, 'index']);
    Route::get('dc/{id}', [DcController::class, 'show']);
    // filter DC by customer (opsional, alternatif query param)
    Route::get('customers/{id}/dc', [DcController::class, 'byCustomer']);

     // Courier Price List
    Route::get('courier-price-list',      [CourierPriceListController::class, 'index']);
    Route::get('courier-price-list/{id}', [CourierPriceListController::class, 'show']);

    // Inventory Balance
    Route::get('inventory-balance',      [InventoryBalanceController::class, 'index']);
    Route::get('inventory-balance/{id}', [InventoryBalanceController::class, 'show']);

    // Insurance Report
    Route::get('insurance-report',      [InsuranceReportController::class, 'index']);
    Route::get('insurance-report/{id}', [InsuranceReportController::class, 'show']);

    // Statutory Sales Report
    Route::get('statutory-sales-report',      [StatutorySalesReportController::class, 'index']);
    Route::get('statutory-sales-report/detail', [StatutorySalesReportController::class, 'show']);

    //POD Hand Over
    Route::get('pod-hand-over',      [PodHandOverController::class, 'index']);
    Route::get('pod-hand-over/detail', [PodHandOverController::class, 'detail']);

    // Outstanding GR
    Route::get('outstanding-gr', [OutstandingGrController::class, 'index']);

    // Outstanding Dispatch
    Route::get('outstanding-dispatch', [OutstandingDispatchController::class, 'index']);

    // Outstanding Dispatch With Freight Cost
    Route::get('outstanding-dispatch-with-freight-cost', [OutstandingDispatchWithFreightCostController::class, 'index']);
});