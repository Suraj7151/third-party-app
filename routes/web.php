<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;


Route::get('/', [UserController::class, 'home'])->name('home'); 
Route::post('/proceed', [UserController::class, 'proceed'])->name('proceed');


Route::get('/showDashboard', [UserController::class, 'showDashboard'])->name('showDashboard'); // Dashboard page
Route::get('/account', [UserController::class, 'account'])->name('account');

Route::post('/proceedDeposit', [UserController::class, 'proceedDeposit'])->name('proceedDeposit');
Route::post('/proceedWithdrawal', [UserController::class, 'proceedWithdrawal'])->name('proceedWithdrawal');