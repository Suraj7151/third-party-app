<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function home()
    {
        return view('home'); // Your form view
    }

    // Handle the form submission
    public function proceed(Request $request)
    {
        // Validate the input user ID
        $request->validate([
            'user_id' => 'required|string|max:255',
        ]);

        // Check if the user already exists in the database
        $user = DB::table('user')->where('user_id', $request->input('user_id'))->first();

        // If the user doesn't exist, create a new user
        if (!$user) {
            DB::table('user')->insert([
                'user_id' => $request->input('user_id'),
                'total_balance' => 0, // Defaults are set in the database schema
                'deposit' => 0,
                'withdrawal' => 0,
                'created_at' => now(),
                'modified_at' => now(),
            ]);
        } else {
            // If the user exists, update the `updated_at` timestamp
            DB::table('user')
                ->where('user_id', $request->input('user_id'))
                ->update(['modified_at' => now()]);
        }

        // Proceed to the next page (for example, the user dashboard)
        return redirect()->route('showDashboard', ['user_id' => $request->input('user_id')]); // Change this route to the actual next page route
    }

    // Show the user dashboard with their data
    public function showDashboard(Request $request)
    {
        // Retrieve the user data based on the user_id
        $user_id = $request->input('user_id');
        $user = DB::table('user')->where('user_id', $user_id)->first();

        // Check if the user exists
        if (!$user) {
            return redirect('/')->with('error', 'User not found!');
        }

        // Pass the user data to the view
        $transactions = DB::table('transaction_log')
            ->where('user_id', $user->user_id)
            ->paginate(6)
            ->appends(['user_id' => $user_id]); // Append user_id to pagination links

        return view('dashboard', ['user' => $user, 'transactions' => $transactions]);
    }

    public function account(Request $request){
        $depositAmount = $request->input('deposit_amount');
        $user_id = $request->input('user_id');

        // Insert into transaction_log and get the newly inserted ID
        $transactionId = DB::table('transaction_log')->insertGetId([
            'user_id' => $user_id,
            'amount' => $depositAmount,
            'available_balance' => 0, 
            'transaction_type' => 'Deposit', 
            'status' => 'Initiated',
            'created_at' => now(),
            'modified_at' => now()
        ]);

        // Call external API to get account details
        $response = Http::post('https://astro.itnbusiness.com/pay/client/initiatePayment', [
            'userid' => $user_id,
            'amount' => $depositAmount
        ]);
        // Debug the response structure
        // dd($response->json());

        // Check if the request was successful
        if ($response->successful() && $response['status'] === 'Success') {
            // Get the account details from the response
            $accounts = $response['data'];
        } else {
            // If API request fails, handle it accordingly
            $accounts = [];
        }


        return view('account')->with([
            'deposit_amount' => $depositAmount,
            'transaction_id' => $transactionId,
            'user_id' => $user_id,
            'accounts' => $accounts
        ]);

    }


    public function proceedDeposit(Request $request){

        // Log the input values for debugging
        Log::info('Proceeding with deposit', [
            'transaction_id' => $request->input('transaction_id'),
            'selected_account' => $request->input('selected_account')
        ]);

        // Get the transaction ID and selected account from the form
        $transactionId = $request->input('transaction_id');
        $selectedAccount = $request->input('selected_account');
        $userId = $request->input('user_id');


        // Update the status to 'Pending' in the transaction_log
        DB::table('transaction_log')
            ->where('id', $transactionId) // Update based on the primary key 'id'
            ->update([
                'status' => 'Pending',
                'modified_at' => now()
            ]);
        
        // Log the redirect to ensure it's being executed
        Log::info('Redirecting to dashboard');
        
        // Redirect or return a view with a success message
        return redirect()->route('showDashboard',['user_id' => $userId])->with('success', 'Your Deposit Request has been submitted.');
    }

    public function proceedWithdrawal(Request $request){
        $user = DB::table('user')->where('user_id', $request->input('user_id'))->first();

        // Ensure that the user has sufficient balance for the withdrawal
        if ($user && $user->total_balance >= $request->input('withdrawal_amount')) {
            DB::table('user')
                ->where('user_id', $request->input('user_id'))
                ->increment('withdrawal', $request->input('withdrawal_amount'));

            DB::table('user')
                ->where('user_id', $request->input('user_id'))
                ->decrement('total_balance', $request->input('withdrawal_amount'));

            // Log the withdrawal transaction
            DB::table('transaction_log')->insert([
                'user_id' => $request->input('user_id'),
                'amount' => $request->input('withdrawal_amount'),
                'transaction_type' => 'Withdrawal',
                'available_balance' => $user->total_balance-$request->input('withdrawal_amount'),
                'status' => 'Pending',
                'created_at' => now(),
                'modified_at' => now(),
            ]);

            return redirect()->route('showDashboard', ['user_id' => $request->input('user_id')]);

        }

        return back()->withErrors(['Insufficient balance' => 'Insufficient balance for this withdrawal.']);
    }


    public function updateTransactionStatus(Request $request){

    }
}
