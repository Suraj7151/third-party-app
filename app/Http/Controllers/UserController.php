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
            ->orderBy('created_at', 'desc')
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
            $orderid = $response['orderid'];
        } else {
            // If API request fails, handle it accordingly
            $accounts = [];
        }


        return view('account')->with([
            'deposit_amount' => $depositAmount,
            'transaction_id' => $transactionId,
            'user_id' => $user_id,
            'accounts' => $accounts,
            'orderid' => $orderid
        ]);

    }




    public function processPayment(Request $request)
    {
        $accountId = $request->input('account_id');
        $userId = $request->input('user_id');
        $depositAmount = $request->input('deposit_amount');
        $orderId = $request->input('order_id');

        // Log the input values for debugging
        Log::info('Proceeding with payment API call', [
            'user_id' => $userId,
            'account_id' => $accountId,
            'deposit_amount' => $depositAmount,
            'order_id' => $orderId
        ]);

        // Make the API call to the external payment API
        try {

            // Get the latest transaction for the user based on the created_at
            $latestTransaction = DB::table('transaction_log')
                ->where('user_id', $userId)  // Filter by user_id
                ->orderBy('created_at', 'desc')  // Get the most recent transaction
                ->first();

            // Check if a transaction exists
            if ($latestTransaction) {
                // Update the latest transaction with the new order_id
                DB::table('transaction_log')
                    ->where('id', $latestTransaction->id)  // Update only the latest transaction
                    ->update([
                        'order_id' => $orderId,
                        'modified_at' => now()
                    ]);
            }


            $response = Http::post('https://astro.itnbusiness.com/pay/client/proceedPayment', [
                'userid' => $userId,
                'amount' => $depositAmount,
                'id' => $accountId,
                'orderid' => $orderId
            ]);

            // Log the response for debugging
            Log::info('Payment API response', ['response' => $response->json()]);

            // Check if the request was successful
            $apiResponse = $response->json();

            // Adjust this condition based on the response structure
            if (isset($apiResponse['status']) && $apiResponse['status'] === 'success') {
                // Log and return success if the payment was processed successfully
                Log::info('Payment processed successfully');
                return response()->json(['success' => true, 'message' => 'Payment processed successfully']);
            } else {
                // Log and return failure if the payment was not processed correctly
                Log::error('Payment API failed', ['response' => $apiResponse]);
                return response()->json(['success' => false, 'message' => 'Failed to process payment']);
            }
        } catch (\Exception $e) {
            Log::error('Error calling payment API', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error calling payment API']);
        }
    }

    public function submitDeposit(Request $request)
    {
        // Log the input values for debugging
        Log::info('Proceeding with deposit', [
            'transaction_id' => $request->input('transaction_id'),
            'selected_account' => $request->input('selected_account')
        ]);

        // Get the transaction ID and selected account from the form
        $transactionId = $request->input('transaction_id');
        $selectedAccount = $request->input('selected_account');
        $userId = $request->input('user_id');
        $utrno = $request->input('utr_no');
        $amount = $request->input('deposit_amount');
        $orderId = $request->input('order_id');
        $image = $request->file('deposit_image');

        Log::info($request->all());
         // Initialize the image path
        $imagePath = null;

        if ($image) {
            // Store the image in the 'public/deposits' directory and get the file path
            $imagePath = $image->store('deposits', 'public');
            Log::info('Image uploaded', ['image_path' => $imagePath]);
        }


        try {
            // Update the status to 'Pending' in the transaction_log
            DB::table('transaction_log')
                ->where('id', $transactionId) // Update based on the primary key 'id'
                ->update([
                    'status' => 'Pending',
                    'utr_no' => $utrno,
                    'modified_at' => now(),
                    'media' => $imagePath
                ]);
            
            // Call the payment API
            $paymentData = [
                'userid' => $userId,
                'amount' => $amount,
                'orderid' => $orderId,
                'utrno' => $utrno
            ];

            // If image is uploaded, include the image path (or base64 encoded image) in the API request
            if ($imagePath) {
                $paymentData['file'] = url('storage/' . $imagePath); // Use the URL of the uploaded image
                
            }
            $response = Http::post('https://astro.itnbusiness.com/pay/client/completeTransaction', $paymentData);

            Log::info('Response Body:', ['body' => $response->body()]);
            // Log the response for debugging
            Log::info('Payment API response', ['response' => $response->json()]);

            // Check if the request was successful
            $apiResponse = $response->json();

            if (isset($apiResponse['status']) && $apiResponse['status'] === 'success') {
                // Log and return success if the payment was processed successfully
                Log::info('Payment completed successfully');
                 // Flash success message to session
                session()->flash('success', 'Your deposit request has been successfully submitted.');

                // Check if the request is an AJAX request or not
                if ($request->ajax()) {
                    return response()->json(['success' => true, 'message' => $apiResponse['message']]);
                } else {
                    // If it's not an AJAX request, redirect to the dashboard
                    return redirect()->route('showDashboard', ['user_id' => $userId])
                        ->with('success', 'Your Deposit Request has been submitted.');
                }
            } else {
                // Log and return failure if the payment was not processed correctly
                Log::error('API request failed with status:', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);
                
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Failed to complete payment']);
                } else {
                    return redirect()->route('showDashboard', ['user_id' => $userId])
                        ->with('error', 'Failed to complete payment');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error calling payment API', ['error' => $e->getMessage()]);
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error calling complete payment API']);
            } else {
                return redirect()->route('showDashboard', ['user_id' => $userId])
                    ->with('error', 'Error calling complete payment API');
            }
        }
    }


    public function proceedWithdrawal(Request $request){
        $user = DB::table('user')->where('user_id', $request->input('user_id'))->first();

        // Ensure that the user has sufficient balance for the withdrawal
        if ($user && $user->total_balance >= $request->input('withdrawal_amount')) {
            try {
                $userId = $request->input('user_id');
                $withdrawalAmount = $request->input('withdrawal_amount');
                
                $response = Http::post('https://astro.itnbusiness.com/pay/client/initiateWithdraw',[
                    'userid' => $userId,
                    'amount' => $withdrawalAmount
                ]);
                if ($response->successful() && $response['status'] === 'Success') {

                    $orderId = $response['orderid'] ?? null;
                    
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
                        'order_id' => $orderId,
                        'available_balance' => $user->total_balance-$request->input('withdrawal_amount'),
                        'status' => 'Pending',
                        'created_at' => now(),
                        'modified_at' => now(),
                    ]);
                    session()->flash('success', 'Your withdrawal request has been successfully submitted.');
                    return redirect()->route('showDashboard', ['user_id' => $request->input('user_id')]);
                    
                }

            } catch (\Throwable $th) {
                Log::error('Withdrawal error: ' . $th->getMessage());
                return back()->withErrors(['error' => 'An error occurred while processing the withdrawal.']);
            }   
        }

        return back()->withErrors(['Insufficient balance' => 'Insufficient balance for this withdrawal.']);
    }

}
