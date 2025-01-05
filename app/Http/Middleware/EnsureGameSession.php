<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureGameSession
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('game_songs') || !$request->session()->has('artist_name')) {
            return redirect()->route('searchArtist.form')->with('error', 'Session expired. Please search for an artist again.');
        }

        return $next($request);
    }
}
