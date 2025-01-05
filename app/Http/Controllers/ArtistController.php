<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DeezerService;

class ArtistController extends Controller
{
    protected $deezerService;

    public function __construct(DeezerService $deezerService)
    {
        $this->deezerService = $deezerService;
    }

    // Step 2: Search Artist Form
    public function searchArtistForm()
    {
        return view('searchArtist');
    }

    // Step 2: Search Artist
    public function searchArtist(Request $request)
    {
        $request->validate(['artist_name' => 'required|string']);

        $artists = $this->deezerService->searchArtist($request->artist_name);

        if (!isset($artists['data']) || empty($artists['data'])) {
            return back()->with('error', 'No artists found for the given name.');
        }

        // Save artists to session
        session(['artists' => $artists['data']]);

        return redirect()->route('selectArtist.form');
    }

    public function selectArtistForm()
    {
        // Retrieve the list of artists from the session
        $artists = session('artists', []);

        // If no artists are found in the session, redirect back to the search form
        if (empty($artists)) {
            return redirect()->route('searchArtist.form')->with('error', 'Please search for an artist first.');
        }

        // Return the view for selecting an artist, passing the list of artists
        return view('selectArtist', compact('artists'));
    }



    // Step 3: Select Artist Form
    public function selectArtist(Request $request)
{
    $request->validate([
        'artist_id' => 'required|integer',
    ]);

    // Retrieve artists from session
    $artists = session('artists', []);

    if (empty($artists)) {
        return redirect()->route('searchArtist.form')->with('error', 'Artist selection has expired. Please search again.');
    }

    // Find the selected artist
    $selectedArtist = collect($artists)->firstWhere('id', $request->artist_id);

    if (!$selectedArtist) {
        return response()->json(['error' => 'Artist not found'], 404);
    }

    // Insert artist into the database if not present
    $artist = \App\Models\Artist::firstOrCreate(
        ['id' => $selectedArtist['id']],
        ['name' => $selectedArtist['name']]
    );

    // Check if a game session already exists for this artist
    if (session()->has('game_songs') && session('artist_id') == $artist->id) {
        return redirect()->route('game.question'); // Redirect to the game if already started
    }

    // Store artist ID and name in session
    // Store artist ID, name, and image in session
    session([
        'artist_id' => $artist->id,
        'artist_name' => $artist->name,
        'artist_image' => $selectedArtist['picture_big'] ?? $selectedArtist['picture'] ?? 'https://via.placeholder.com/150',
    ]);


    // Check if songs exist for this artist
    $existingSongs = \App\Models\Song::where('artist_id', $artist->id)->exists();

    if (!$existingSongs) {
        // Fetch albums from the Deezer API
        $albums = $this->deezerService->getArtistAlbums($artist->id);

        foreach ($albums as $albumData) {
            $album = \App\Models\Album::firstOrCreate(
                ['id' => $albumData['id']],
                [
                    'artist_id' => $artist->id,
                    'name' => $albumData['title'],
                    'image' => $albumData['cover_big'], // Save album image
                ]
            );

            // Fetch songs for this album
            $songs = $this->deezerService->getAlbumTracks($album->id);

            foreach ($songs as $songData) {
                // Skip songs containing "(Off Vocal)"
                if (strpos($songData['title'], '(Off Vocal)') !== false) {
                    continue;
                }

                \App\Models\Song::firstOrCreate(
                    ['id' => $songData['id']],
                    [
                        'artist_id' => $artist->id,
                        'album_id' => $album->id,
                        'name' => $songData['title'],
                        'preview' => $songData['preview'], // Save the music preview link
                    ]
                );
            }
        }
    }

    // Fetch 10 random songs for the game
    $randomSongs = \App\Models\Song::where('artist_id', $artist->id)->inRandomOrder()->limit(10)->get();

    if ($randomSongs->count() < 10) {
        return redirect()->route('searchArtist.form')->with('error', 'Not enough songs available to start the game.');
    }

    // Prepare the game session data
    session([
        'game_songs' => $randomSongs,
        'score' => 0,
        'current_question' => 0,
        'artist_name' => $artist->name,
    ]);

    // Redirect to the first question of the game
    return redirect()->route('game.question');
}

    





}
