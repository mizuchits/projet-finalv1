<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class OsuTokenService
{
    private string $token = '';
    private int $expiresAt = 0;

    public function __construct(
        private HttpClientInterface $client,
        private string $clientId,
        private string $clientSecret,
        private AdapterInterface $cache
    ) {
    }

    public function getAccessToken(): string
    {
        if ($this->token && time() < $this->expiresAt) {
            return $this->token;
        }

        $cacheKey = 'osu_client_token';
        $cached = $this->cache->getItem($cacheKey);

        if ($cached->isHit()) {
            $data = $cached->get();
            $this->token = $data['token'];
            $this->expiresAt = $data['expires'];
            return $this->token;
        }

        $response = $this->client->request('POST', 'https://osu.ppy.sh/oauth/token', [
            'json' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope'         => 'public',
            ],
        ]);

        $data = $response->toArray();

        $this->token = $data['access_token'];
        $this->expiresAt = time() + $data['expires_in'] - 60;

        $cached->set(['token' => $this->token, 'expires' => $this->expiresAt]);
        $cached->expiresAfter($data['expires_in'] - 300);
        $this->cache->save($cached);

        return $this->token;
    }
}