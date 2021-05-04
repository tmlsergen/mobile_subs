<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|-------------------,-------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::prefix('v1')->group(function (){
    // register
    Route::post('/auth/device/register', \App\Http\Controllers\Api\Auth\RegisterController::class)->name('auth.register');

    Route::post('/subscriptions/purchase', \App\Http\Controllers\Api\Subscription\PurchaseController::class)->name('subscription.purchase')->middleware('auth:device');
    Route::get('/subscriptions/check', \App\Http\Controllers\Api\Subscription\CheckSubscriptionController::class)->name('subscription.check')->middleware('auth:device');

});


