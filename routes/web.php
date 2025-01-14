<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;


Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');


// Home route (redirect to login if not authenticated)
Route::get('/', function () {
    // Redirect to dashboard if the user is authenticated
    if (Auth::check()) {
        return redirect()->route('showDashboard');
    }

    // Otherwise, redirect to the login page
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/showDashboard', [UserController::class, 'showDashboard'])->name('showDashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


Route::post('/proceed', [UserController::class, 'proceed'])->name('proceed');

Route::get('/account', [UserController::class, 'account'])->name('account');
Route::post('/process-payment', [USerController::class, 'processPayment'])->name('processPayment');

Route::post('/submitDeposit', [UserController::class, 'submitDeposit'])->name('submitDeposit');
Route::post('/proceedWithdrawal', [UserController::class, 'proceedWithdrawal'])->name('proceedWithdrawal');
Route::get('/password/updateForm', function(){
    return view('update_password');
});
Route::put('/password/update', [UserController::class, 'updatePassword'])->name('password.update');

Route::post('/raise-ticket', [UserController::class, 'raiseTicket'])->name('raiseTicket');
