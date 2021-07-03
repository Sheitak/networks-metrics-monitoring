<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AuthenticationService
{
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function isAuthenticated(): bool
    {
        session_start();

        if (empty($_SESSION['user_access_token']) || empty($_SESSION['user_id'])) {
            return false;
        }

        $client = HttpClient::create();
        $response = $client->request(
            'GET',
            "https://graph.facebook.com/v5.0/debug_token?input_token=" .
            $_SESSION['user_access_token'] .
            "&access_token=" .
            $_SESSION['user_access_token']
        );

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            return false;
        }

        $content = $response->toArray();
        return $content['data']['is_valid'];
    }
}
