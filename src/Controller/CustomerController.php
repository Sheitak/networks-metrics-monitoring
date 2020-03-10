<?php

namespace App\Controller;

use App\Service\AuthenticationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CustomerRepository;
use \DateTime;

class CustomerController extends AbstractController
{
    /**
     * @Route("/customer/{id}", name="customer", methods={"GET"})
     */
    public function index(
        string $id,
        AuthenticationService $authService
    ): Response {
        // Check if user is authenticated
        if (!$authService->isAuthenticated()) {
            return $this->redirectToRoute('login');
        }

        // Get pages access token
        $client = HttpClient::create();
        $response = $client->request(
            'GET',
            "https://graph.facebook.com/v5.0/" .
            $_SESSION['user_id'] .
            "/accounts?access_token=" .
            $_SESSION['user_access_token']
        );
        $content = $response->toArray();
        $pageDatas = $content['data'];

        $matchingPageDatas = array_values(array_filter($pageDatas, function ($pageData) use ($id) {
            return $pageData['id'] === $id;
        }));

        $matchingPageData = [];
        $matchingPageDataImp = [];
        $matchingPageDataEngu = [];
        $matchingPageDataLike = [];

        if (!empty($matchingPageDatas)) {
            $matchingPageData = $matchingPageDatas[0];

            // Get metrics of each pages
            $pageId = $matchingPageData['id'];
            $pageAccessToken = $matchingPageData['access_token'];
            $currentDate = new DateTime(date('c', strtotime('+1 days')));
            $previousDate = new DateTime(date('c', strtotime('-30 days')));
            $responseImp = $client->request(
                'GET',
                "https://graph.facebook.com/v5.0/$pageId/insights/" .
                "?metric=page_posts_impressions,page_posts_impressions_paid" .
                ",page_posts_impressions_organic" .
                "&access_token=$pageAccessToken" .
                "&period=day" .
                "&since=" . $previousDate->format('Y-m-d') .
                "&until=" . $currentDate->format('Y-m-d')
            );

            $responseEngu = $client->request(
                'GET',
                "https://graph.facebook.com/v5.0/$pageId/insights/" .
                "?metric=page_engaged_users,page_actions_post_reactions_like_total" .
                "&access_token=$pageAccessToken" .
                "&period=day" .
                "&since=" . $previousDate->format('Y-m-d') .
                "&until=" . $currentDate->format('Y-m-d')
            );

            $responseLike = $client->request(
                'GET',
                "https://graph.facebook.com/v5.0/$pageId/insights/" .
                "?metric=page_fans" .
                "&access_token=$pageAccessToken" .
                "&period=day" .
                "&since=" . $previousDate->format('Y-m-d') .
                "&until=" . $currentDate->format('Y-m-d')
            );

            $contentImp = $responseImp->toArray();
            $contentEngu = $responseEngu->toArray();
            $contentLike = $responseLike->toArray();

            $matchingPageDataImp['metrics'] = $contentImp['data'];
            $matchingPageDataEngu['metrics'] = $contentEngu['data'];
            $matchingPageDataLike['metrics'] = $contentLike['data'];
        }

        $randomNumber = rand(1, 5);

        // Make packages of 3 customers for carousel
        // Pass datas to Twig
        return $this->render('customer/index.html.twig', [
            'customer' => $matchingPageData,
            'customerImp' => $matchingPageDataImp,
            'customerEngu' => $matchingPageDataEngu,
            'customerLike' => $matchingPageDataLike,
            'randomNumber' => $randomNumber
        ]);
    }
}
