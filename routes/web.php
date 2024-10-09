<?php

use App\Http\Controllers\OrderStatusController;
use Illuminate\Support\Facades\Route;
use App\Filament\Admin\Resources\UserResource;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ship', [OrderStatusController::class, 'index']);

Route::post('/track-shipment',[OrderStatusController::class,'show'])->name('trackShipment');
