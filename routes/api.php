<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiController;

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
Route::post('/login', [AuthController::class, 'login']);
//Route::get('/resetpassword', [ApiController::class, 'resetPassword']);

Route::middleware(['auth:api'])->group(function() {
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::post('/requestLoan', [ApiController::class, 'requestLoan']);
    Route::get('/approveLoan/{id}', [ApiController::class, 'approveLoan']);
    Route::get('/loans', [ApiController::class, 'getLoans']);
    Route::post('/addRepayment/{loanId}', [ApiController::class, 'addRepayment']);
});
