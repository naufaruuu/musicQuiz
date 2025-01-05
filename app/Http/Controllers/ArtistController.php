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
    
        // Store artist ID and name in session
        session([
            'artist_id' => $artist->id,
            'artist_name' => $artist->name,
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
    
        // Redirect to game start
        return redirect()->route('game.start');
    }
    





}
