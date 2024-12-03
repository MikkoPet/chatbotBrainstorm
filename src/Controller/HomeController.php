<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/test-mercure', name: 'test_mercure', methods: ['GET', 'POST'])]
    public function testMercure(Request $request, HubInterface $hub): Response
    {
        if ($request->isMethod('POST')) {
            $data    = json_decode($request->getContent(), true);
            $message = $data['message'] ?? 'No message provided';

            $update = new Update(
                'https://example.com/books/1',
                json_encode(['status' => $message])
            );

            $hub->publish($update);

            return $this->json(['status' => 'Message sent']);
        }

        return $this->render('home/test_mercure.html.twig', [
            'message' => 'Mercure test page. Send a message to see it appear below.',
        ]);
    }
}