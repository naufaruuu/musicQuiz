<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DeezerService
{
    protected $client;
    protected $maxRetries = 3; // Number of retries
    protected $retryDelay = 100; // Initial delay in milliseconds

    public function __construct()
    {
        $handlerStack = \GuzzleHttp\HandlerStack::create();
        
        // Add retry middleware
        $handlerStack->push($this->retryMiddleware());

        $this->client = new Client([
            'base_uri' => 'https://api.deezer.com/',
            'handler' => $handlerStack,
        ]);
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

    public function getTrackDetails($trackId)
    {
        return Cache::remember("track_{$trackId}", now()->addHours(24), function () use ($trackId) {
            $url = "https://api.deezer.com/track/{$trackId}";
            $response = Http::retry($this->maxRetries, $this->retryDelay, function ($exception) {
                return $exception instanceof RequestException;
            })->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        });
    }

    private function retryMiddleware()
    {
        return Middleware::retry(
            function ($retries, RequestInterface $request, ResponseInterface $response = null, RequestException $exception = null) {
                // Retry for server errors (5xx) or connection errors
                if ($retries >= $this->maxRetries) {
                    return false;
                }

                if ($response && $response->getStatusCode() >= 500) {
                    return true;
                }

                if ($exception instanceof RequestException) {
                    return true;
                }

                return false;
            },
            function ($retries) {
                // Exponential backoff delay
                return $this->retryDelay * (2 ** $retries);
            }
        );
    }
}
