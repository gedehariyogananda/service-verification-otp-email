<?php

use App\Http\Controllers\API\Auth\AuthenticateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthenticateController::class)->group(function () {
    // authenticate
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('verify/otp/register', 'verifyOtpRegister');
    Route::post('verify/otp/newotp', 'newOtp');

    // forgot password
    Route::post('forgot', 'forgot');
    Route::post('verify/otp/forgot', 'verifyOtpForgot');
    Route::post('reset', 'reset');

    // auth logout
    Route::post('logout', 'logout');
});
