<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class AuthenticationService
{
    public function isAuthenticated(): bool
    {
        // Start session
        session_start();

        // Check if session variables are filled
        if (empty($_SESSION['user_access_token']) || empty($_SESSION['user_id'])) {
            return false;
        }

        // Create request to check the status of the token
        $client = HttpClient::create();
        $response = $client->request(
            'GET',
            "https://graph.facebook.com/v5.0/debug_token?input_token=" .
            $_SESSION['user_access_token'] .
            "&access_token=" .
            $_SESSION['user_access_token']
        );

        // Check if the request has failed
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            return false;
        }

        // Check if token is valid
        $content = $response->toArray();
        return $content['data']['is_valid'];
    }
}
