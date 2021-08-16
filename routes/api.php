<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/invoices', [InvoiceController::class, 'query']);
Route::get('/filters', [FilterController::class, 'query']);
Route::get('/customers', [CustomerController::class, 'index']);
Route::get('/accounts', [AccountController::class, 'index']);

Route::post('/batch', [BatchController::class, 'store']);
