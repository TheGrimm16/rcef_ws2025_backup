<?php

namespace App\Http\Controllers\SeedReplacement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\SeedReplacementUser;

class SRAuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // Pass old input (email) back to view
        return view('seed_replacement.login', [
            'email' => old('email', $request->session()->get('email', ''))
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Save email to session to refill on error
        $request->session()->flash('email', $credentials['email']);

        // Fetch user by email
        $user = SeedReplacementUser::where('email', $credentials['email'])->first();

        if (!$user) {
            return redirect()->back()->withErrors(['email' => 'User not found']);
        }

        // Check password
        if (!Hash::check($credentials['password'], $user->password)) {
            return redirect()->back()->withErrors(['password' => 'Invalid password']);
        }

        // Login using custom guard
        Auth::guard('seed_replacement')->login($user);

        return redirect()->intended(route('replacement.dashboard'));

    }

    public function logout()
    {
        Auth::guard('seed_replacement')->logout();
        return redirect()->route('replacement.login');
    }
}
