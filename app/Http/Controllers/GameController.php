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

    $deezerService = new \App\Services\DeezerService();
    
    // Fetch albums for the artist
    $albums = $deezerService->getArtistAlbums($artistId);

    // Fetch tracks from albums
    $tracks = collect();
    foreach ($albums as $album) {
        $albumTracks = $deezerService->getAlbumTracks($album['id']);
        $tracks = $tracks->merge($albumTracks);
    }

    // Ensure at least 10 tracks are available
    if ($tracks->count() < 10) {
        return redirect()->route('searchArtist.form')->with('error', 'Not enough songs available for the selected artist.');
    }

    // Randomly select 10 tracks and fetch preview URLs
    $gameTracks = $tracks->shuffle()->take(10)->map(function ($track) {
        return [
            'id' => $track['id'],
            'title' => $track['title'],
            'preview' => $track['preview'], // Preview URL from API
            'album' => $track['album']['title'],
            'image' => $track['album']['cover_medium'], // Album cover
        ];
    });

    // Store game data in the session
    session([
        'game_songs' => $gameTracks->toArray(),
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

    // Generate choices (ensure unique tracks)
    $choices = collect($gameSongs)
        ->filter(fn($song) => $song['id'] != $currentSong['id'])
        ->shuffle()
        ->take(2)
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
    
        // Save user's answer in the session
        session()->put("answers.{$currentQuestion}", $request->selected_song_id);
    
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
    $artistId = session('artist_id');
    $userId = session('user_id'); // Get the user ID from the session

    if (!$userId) {
        return redirect()->route('login.form')->with('error', 'You must be logged in to play the game.');
    }

    $startTime = session('game_start_time');
    $duration = $startTime ? now()->diffInSeconds($startTime) : 0;

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

    // Prepare questions for corrections
    $gameSongs = session('game_songs', []);
    $questions = [];
    foreach ($gameSongs as $index => $song) {
        $userAnswerId = session("answers.{$index}", null);
        $userAnswer = null;
    
        // Retrieve the name of the song the user selected (if they answered)
        if ($userAnswerId) {
            $answeredSong = \App\Models\Song::find($userAnswerId);
            $userAnswer = $answeredSong ? $answeredSong->name : 'Unknown';
        } else {
            $userAnswer = 'Not Answered';
        }
    
        // Ensure proper comparison
        $isCorrect = intval($userAnswerId) === intval($song->id);
    
        $questions[] = [
            'image' => $song->album->image,
            'user_answer' => $userAnswer,
            'correct_answer' => $song->name,
            'preview' => $song->preview,
            'is_correct' => $isCorrect,
        ];
    }
    

    // Clear session data
    session()->forget(['game_songs', 'score', 'current_question', 'artist_name', 'answers']);

    return view('gamefinish', [
        'score' => $score,
        'artistName' => $artistName,
        'artistImage' => session('artist_image'),
        'duration' => $duration,
        'topRecords' => $topRecords,
        'questions' => $questions,
    ]);
}

    





}
