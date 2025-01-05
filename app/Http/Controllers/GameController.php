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

    // Store the start time for the current question
    session(['question_start_time' => now()]);

    $currentSong = $gameSongs[$currentQuestion];

    // Fetch the latest track details dynamically from Deezer
    $deezerService = app()->make(\App\Services\DeezerService::class);
    $trackDetails = $deezerService->getTrackDetails($currentSong['id']);

    if ($trackDetails && isset($trackDetails['preview'])) {
        $currentSong['preview'] = $trackDetails['preview'];
    }

    // Generate choices
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
    $isCorrect = $request->selected_song_id == $currentSong['id'];
    if ($isCorrect) {
        $score += 1; // Correct answer
    }

    // Calculate elapsed time for this question
    $startTime = session('question_start_time');
    $elapsedTime = $startTime ? now()->diffInSeconds($startTime) : 0;

    // Save user's answer and elapsed time in the session
    session()->put("answers.{$currentQuestion}", [
        'selected_song_id' => $request->selected_song_id,
        'elapsed_time' => $elapsedTime,
    ]);

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

    $userId = session('user_id'); // Ensure user is logged in
    if (!$userId) {
        return redirect()->route('login.form')->with('error', 'You must be logged in to play the game.');
    }

    // Prepare questions with corrections and calculate total duration
    $gameSongs = session('game_songs', []);
    $questions = [];
    $totalElapsedTime = 0; // Initialize total elapsed time

    foreach ($gameSongs as $index => $song) {
        $answerData = session("answers.{$index}", []);
        $userAnswerId = $answerData['selected_song_id'] ?? null;
        $elapsedTime = $answerData['elapsed_time'] ?? 0;

        $totalElapsedTime += $elapsedTime; // Add to total elapsed time

        $userAnswer = null;

        if ($userAnswerId) {
            $answeredSong = \App\Models\Song::find($userAnswerId);
            $userAnswer = $answeredSong ? $answeredSong->name : 'Unknown';
        } else {
            $userAnswer = 'Not Answered';
        }

        $isCorrect = intval($userAnswerId) === intval($song->id);

        $questions[] = [
            'image' => $song->album->image,
            'user_answer' => $userAnswer,
            'correct_answer' => $song->name,
            'preview' => $song->preview,
            'is_correct' => $isCorrect,
            'elapsed_time' => $elapsedTime,
        ];
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
        ->with('user') // Ensure user relationship is loaded
        ->get();

    // Clear session data
    session()->forget(['game_songs', 'score', 'current_question', 'artist_name', 'answers', 'question_start_time']);

    // Pass all required data to the view
    return view('gamefinish', [
        'score' => $score,
        'artistName' => $artistName,
        'artistImage' => session('artist_image'),
        'duration' => $totalElapsedTime, // Use the calculated total elapsed time
        'topRecords' => $topRecords,
        'questions' => $questions,
    ]);
}


}
