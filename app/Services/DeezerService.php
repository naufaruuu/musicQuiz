<?php
namespace App\Services;

use GuzzleHttp\Client;

class DeezerService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.deezer.com/']);
    }

    public function searchArtist($query)
    {
        $response = $this->client->get('search/artist', [
            'query' => ['q' => $query],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getArtistAlbums($artistId)
    {
        $albums = [];
        $nextUrl = "artist/{$artistId}/albums";

        while ($nextUrl) {
            $response = $this->client->get($nextUrl);
            $data = json_decode($response->getBody(), true);
            $albums = array_merge($albums, $data['data']);
            $nextUrl = $data['next'] ?? null; // Continue if there are more pages
        }

        return $albums;
    }

    
    public function getAlbumTracks($albumId)
    {
        $response = $this->client->get("album/{$albumId}/tracks");
        $data = json_decode($response->getBody(), true);
    
        return $data['data'] ?? [];
    }
    

}
