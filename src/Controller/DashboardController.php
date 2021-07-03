<?php

namespace App\Controller;

use App\Repository\CustomerRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\AuthenticationService;
use Symfony\Component\HttpClient\HttpClient;
use \DateTime;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="dashboard", methods={"GET"})
     * @throws Exception
     * @throws TransportExceptionInterface|DecodingExceptionInterface
     */
    public function index(AuthenticationService $authService): Response
    {
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

        // Get metrics of each pages
        foreach ($pageDatas as $key => $pageData) {
            $pageId = $pageData['id'];
            $pageAccessToken = $pageData['access_token'];
            $currentDate = new DateTime(date('c', strtotime('+1 days')));
            $previousDate = new DateTime(date('c', strtotime('-30 days')));
            $response = $client->request(
                'GET',
                "https://graph.facebook.com/v5.0/$pageId/insights/" .
                "?metric=page_posts_impressions,page_posts_impressions_paid" .
                ",page_posts_impressions_organic,page_engaged_users," .
                "page_actions_post_reactions_like_total,page_fans" .
                "&access_token=$pageAccessToken" .
                "&period=day" .
                "&since=" . $previousDate->format('Y-m-d') .
                "&until=" . $currentDate->format('Y-m-d')
            );
            $content = $response->toArray();
            $pageDatas[$key]['metrics'] = $content['data'];
        }

        // Make packages of 3 customers for carousel
        $customerPackages = [];
        $customerPackage = [];

        foreach ($pageDatas as $customer) {
            $customerPackage[] = $customer;
            if (count($customerPackage) === 3) {
                $customerPackages[] = $customerPackage;
                $customerPackage  = [];
            }
        }

        $randomNumber = random_int(1, 5);

        // Pass datas to Twig
        return $this->render('dashboard/index.html.twig', [
            'customerPackages' => $customerPackages,
            'pageDatas' => $pageDatas,
            'randomNumber' => $randomNumber
        ]);
    }
}
