<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        // Validate the login request
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            // Get the authenticated user
            $authUser = Auth::user(); // This retrieves the authenticated user from the default users table

            // Retrieve user_id from the authenticated user
            $user_id = $authUser->id;

            // Check if the user_id exists in the custom 'user' table
            $user = DB::table('user')->where('user_id', $user_id)->first();

            // If user_id doesn't exist in the 'user' table, insert a new entry
            if (!$user) {
                DB::table('user')->insert([
                    'user_id' => $user_id,
                    'total_balance' => 0,
                    'deposit' => 0,
                    'withdrawal' => 0,
                    'created_at' => now(),
                    'modified_at' => now(),
                ]);
            }

            // Redirect to dashboard with the user_id
            return redirect()->route('showDashboard', ['user_id' => $user_id]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
