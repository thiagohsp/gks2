<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Auth
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('login', [LoginController::class, 'login'])->name('login.attempt')->middleware('guest');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/notas-fiscais', [InvoiceController::class, 'index'])->name('dashboard');
    Route::get('/lotes', [BatchController::class, 'index'])->name('lotes');
    Route::get('/lotes/{batchId}/contas_a_recebers', [BillController::class, 'index'])->name('contas_a_recebers');
    Route::get('/file', [DownloadController::class, 'getBatchDownload'])->name('lotes_download');
    Route::get('/', [InvoiceController::class, 'index']);

});


