<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Show the login form
    public function showLoginForm()
    {
        return view('login');
    }

    // Handle login or registration
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Check if the user exists
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            // Create the user if it doesn't exist
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);

            // Save user session
            $request->session()->put('user_id', $user->id); // Save user ID in session
            $request->session()->put('username', $user->username);

            return redirect()->route('searchArtist.form')->with('message', 'Success: New user created!');
        }

        // If user exists, verify the password
        if (Hash::check($request->password, $user->password)) {
            // Save user session
            $request->session()->put('user_id', $user->id); // Save user ID in session
            $request->session()->put('username', $user->username);

            return redirect()->route('searchArtist.form')->with('message', 'Success');
        }

        return back()->with('error', 'Password incorrect or username already taken. Please try again.');
    }

    // Step 2 Placeholder
    public function step2()
    {
        return response()->json(['message' => 'Step 2: Success'], 200);
    }

    // Logout the user
    public function logout(Request $request)
    {
        $request->session()->forget(['user_id', 'username']); // Clear both user_id and username
        return redirect()->route('login.form')->with('message', 'Logged out successfully.');
    }
}
