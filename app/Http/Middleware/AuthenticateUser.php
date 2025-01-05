<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateUser
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the username exists in the session
        if (!$request->session()->has('username')) {
            return redirect()->route('login.form')->with('error', 'Please log in first.');
        }

        return $next($request);
    }
}
