<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    public function updateTransactionStatus(Request $request)
    {
        Log::info('Received webhook', ['data' => $request->all()]);

        try {
            // Get data from the webhook
            $orderId = $request->input('orderId');
            $status = $request->input('status'); 
            $transactionType = $request->input('transactionType'); 
            $amount = $request->input('amount'); 

            // Validate the request (make sure it contains the necessary fields)
            if (!$orderId || !$status || !$transactionType || !$amount) {
                return response()->json(['error' => 'Invalid webhook data'], 400);
            }

            // Check if the transaction is of type 1 (Deposit)
            if ($transactionType == 1 && $status == 'Success') {
                // Find the transaction based on the order_id
                $transaction = DB::table('transaction_log')
                    ->where('order_id', $orderId)
                    ->first();

                if ($transaction) {
                    // Update the transaction status and modify the transaction log
                    DB::table('transaction_log')
                        ->where('order_id', $orderId)
                        ->update([
                            'status' => $status,
                            'modified_at' => now(),
                        ]);

                    // Find the user associated with this transaction (assuming user_id is in the transaction table)
                    $userId = $transaction->user_id;

                    // Update the user's deposit and total balance
                    DB::table('user')
                        ->where('user_id', $userId)
                        ->increment('deposit', $amount); // Add the deposit amount to the deposit column
                    DB::table('user')
                        ->where('user_id', $userId)
                        ->increment('total_balance', $amount); // Add the same amount to the total_balance column

                    Log::info('Transaction status updated and user balance updated', [
                        'order_id' => $orderId,
                        'status' => $status,
                        'user_id' => $userId,
                        'deposit_amount' => $amount,
                    ]);

                    return response()->json(['message' => 'Transaction status and user balance updated successfully'], 200);
                } else {
                    Log::error('Transaction not found', ['order_id' => $orderId]);
                    return response()->json(['error' => 'Transaction not found'], 404);
                }
            } else {
                $transaction = DB::table('transaction_log')
                ->where('order_id', $orderId)
                ->first();

                if ($transaction) {
                    // Update the transaction status to 'Success' if the status is 'Success'
                    DB::table('transaction_log')
                        ->where('order_id', $orderId)
                        ->update([
                            'status' => $status,
                            'modified_at' => now(),
                        ]);

                    // If status is Failure, update user withdrawal and available_balance
                    if ($status == 'Failure') {
                        $userId = $transaction->user_id;

                        // Subtract the amount from the withdrawal column
                        DB::table('user')
                            ->where('user_id', $userId)
                            ->decrement('withdrawal', $amount);

                        // Add the amount to the available_balance column
                        DB::table('user')
                            ->where('user_id', $userId)
                            ->increment('total_balance', $amount);

                        Log::info('Transaction status updated and user withdrawal adjusted for failure', [
                            'order_id' => $orderId,
                            'status' => $status,
                            'user_id' => $userId,
                            'amount' => $amount,
                        ]);
                    }

                    return response()->json(['message' => 'Transaction status updated successfully'], 200);
                } else {
                    Log::error('Transaction not found for withdrawal', ['order_id' => $orderId]);
                    return response()->json(['error' => 'Transaction not found'], 404);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error processing webhook', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error processing webhook'], 500);
        }
    }

}
