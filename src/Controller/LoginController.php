<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class LoginController extends AbstractController
{
    /**
     * @Route("/", name="login", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        // Go to Facebook login to get short-live authentication code
        $redirectUri = $request->getSchemeAndHttpHost() . '/authenticate';
        $facebookAppId = $_ENV['FACEBOOK_APP_ID'];
        $randomString = md5((string)time());

        return $this->redirect(
            "https://www.facebook.com/v5.0/dialog/oauth" .
            "?client_id=$facebookAppId" .
            "&redirect_uri=$redirectUri" .
            "&state=$randomString" .
            "&granted_scopes=manage_pages,read_insights"
        );
    }

    /**
     * @Route("/authenticate", name="authenticate", methods={"GET"})
     * @param Request $request
     * @return Response
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function authenticate(Request $request): Response
    {
        session_start();
        $client = HttpClient::create();

        // Get short-live authentication code from Facebook login redirection
        $code = $request->query->get('code');

        // Get login-live user access token from short-live authentication code
        $facebookAppId = $_ENV['FACEBOOK_APP_ID'];
        $facebookSecretKey = $_ENV['FACEBOOK_SECRET_KEY'];
        $redirectUri = $request->getSchemeAndHttpHost() . '/authenticate';
        $response = $client->request(
            'GET',
            "https://graph.facebook.com/v5.0/oauth/access_token" .
            "?client_id=$facebookAppId" .
            "&redirect_uri=$redirectUri" .
            "&client_secret=$facebookSecretKey" .
            "&code=$code"
        );
        $content = $response->toArray();
        $_SESSION['user_access_token'] = $content['access_token'];

        // Get user id from long-live user access token
        $response = $client->request(
            'GET',
            "https://graph.facebook.com/v5.0/me?access_token=" . $_SESSION['user_access_token']
        );
        $content = $response->toArray();
        $_SESSION['user_id']  = $content['id'];

        return $this->redirectToRoute('dashboard');
    }
}
