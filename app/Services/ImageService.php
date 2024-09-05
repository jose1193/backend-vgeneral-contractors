<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Pool;

class ImageService
{
    protected $guzzleClient;

    public function __construct()
    {
        $this->guzzleClient = new Client();
    }

    public function fetchImagesAsync(array $imageUrls): array
    {
        $requests = function () use ($imageUrls) {
            foreach ($imageUrls as $key => $url) {
                yield $key => new Request('GET', $url);
            }
        };

        $results = [];
        $pool = new Pool($this->guzzleClient, $requests(), [
            'fulfilled' => function ($response, $key) use (&$results) {
                $results[$key] = 'data:image/jpeg;base64,' . base64_encode($response->getBody()->getContents());
            },
            'rejected' => function (RequestException $exception, $key) use (&$results) {
                Log::error("Failed to fetch image from index $key", ['error' => $exception->getMessage()]);
                $results[$key] = null;
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        return $results;
    }

    public function getImageBase64(string $url, string $type = 'image/jpeg'): string
    {
        try {
            $response = $this->guzzleClient->get($url);
            return "data:$type;base64," . base64_encode($response->getBody()->getContents());
        } catch (RequestException $e) {
            Log::error('Error fetching image', ['url' => $url, 'error' => $e->getMessage()]);
            return "data:$type;base64,";
        }
    }
}
