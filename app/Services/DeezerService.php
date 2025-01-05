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
    $response = $this->client->get("artist/{$artistId}/albums");
    $data = json_decode($response->getBody(), true);

    return $data['data'] ?? [];
}

public function getAlbumTracks($albumId)
{
    $response = $this->client->get("album/{$albumId}/tracks");
    $data = json_decode($response->getBody(), true);

    return $data['data'] ?? [];
}


    public function getTrackPreview($trackId)
    {
        $response = $this->client->get("track/{$trackId}");
        $data = json_decode($response->getBody(), true);

        return $data['preview'] ?? null; // Return the preview URL if available
    }


    

}
