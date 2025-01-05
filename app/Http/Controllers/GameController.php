<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function startGame()
    {
        $artistId = session('artist_id'); // Retrieve artist ID from session

        if (!$artistId) {
            return redirect()->route('searchArtist.form')->with('error', 'Please select an artist first.');
        }

        // Get 10 random songs for the selected artist
        $songs = \App\Models\Song::where('artist_id', $artistId)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        if ($songs->count() < 10) {
            return redirect()->route('searchArtist.form')->with('error', 'Not enough songs available for the selected artist.');
        }

        // Store game data in the session
        session([
            'game_songs' => $songs,
            'score' => 0,
            'current_question' => 0,
        ]);

        return redirect()->route('game.question');
    }



    public function showQuestion()
    {
        $gameSongs = session('game_songs', []);
        $currentQuestion = session('current_question', 0);

        if ($currentQuestion >= count($gameSongs)) {
            return redirect()->route('game.finish');
        }

        $currentSong = $gameSongs[$currentQuestion];
        $artistId = session('artist_id');

        // Ensure choices are from the same artist
        $choices = \App\Models\Song::where('artist_id', $artistId)
            ->where('id', '!=', $currentSong->id)
            ->inRandomOrder()
            ->limit(2)
            ->get()
            ->push($currentSong)
            ->shuffle();

        return view('gamestart', [
            'currentSong' => $currentSong,
            'choices' => $choices,
            'questionNumber' => $currentQuestion + 1,
        ]);
    }


    public function answerQuestion(Request $request)
    {
        $gameSongs = session('game_songs', []);
        $currentQuestion = session('current_question', 0);
        $score = session('score', 0);

        $currentSong = $gameSongs[$currentQuestion];

        // Check if the selected song is correct
        if ($request->selected_song_id == $currentSong->id) {
            $score += 1; // Correct answer
        }

        // Update session data
        session([
            'current_question' => $currentQuestion + 1,
            'score' => $score,
        ]);

        return redirect()->route('game.question');
    }

    public function finishGame()
    {
        $score = session('score', 0);
        $artistName = session('artist_name', 'Unknown Artist');
        $artistId = session('game_songs')->first()->artist_id ?? null;
        $userId = session('user_id'); // Get the user ID from the session

        if (!$userId) {  
            return redirect()->route('login.form')->with('error', 'You must be logged in to play the game.');
        }

        // Insert the game record
        \App\Models\Record::create([
            'user_id' => $userId,
            'artist_id' => $artistId,
            'score' => $score,
        ]);

        // Fetch the top 10 scores for the artist
        $topRecords = \App\Models\Record::where('artist_id', $artistId)
            ->orderBy('score', 'desc')
            ->limit(10)
            ->with('user') // Eager load user relationship
            ->get();

        // Clear session data
        session()->forget(['game_songs', 'score', 'current_question', 'artist_name']);

        return view('gamefinish', [
            'score' => $score,
            'artistName' => $artistName,
            'topRecords' => $topRecords,
        ]);
    }



}
