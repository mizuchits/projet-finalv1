<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OsuapiService
{
    public function __construct(
        private HttpClientInterface $client,
        private OsuTokenService $tokenService
    ) {
    }

    public function searchBeatmaps(string $query, int $limit = 20): array
    {
        $token = $this->tokenService->getAccessToken();
        $response = $this->client->request('GET', 'https://osu.ppy.sh/api/v2/beatmapsets/search', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'query' => [
                'q' => $query,
                'limit' => min($limit, 50),
            ],
        ]);

        $status = $response->getStatusCode();
        if ($status !== 200) {
            return [];
        }

        $data = $response->toArray();

        return $data['beatmapsets'] ?? [];
    }
    public function getBeatmapset(int $beatmapsetId): ?array
{
    $token = $this->tokenService->getAccessToken();

    if (empty($token)) {
        dd('Token vide ! Vérifie OsuTokenService et .env.local');
    }

    $fullUrl = "https://osu.ppy.sh/api/v2/beatmapsets/{$beatmapsetId}";

    $response = $this->client->request('GET', $fullUrl, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ],
    ]);

    $status = $response->getStatusCode();
    $content = $response->getContent(false);

    if ($status !== 200) {
        return null;
    }

    return $response->toArray();
}
}