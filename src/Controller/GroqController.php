<?php

namespace App\Controller;

use App\Service\GroqApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class GroqController extends AbstractController
{
    private $groqApiService;

    public function __construct(GroqApiService $GroqApiService)
    {
        $this->groqApiService = $GroqApiService;
    }

    /**
     * Handles executing a custom Groq query.
     */
    #[Route("/groq/prompt", name: "app_groq_prompt", methods: ["POST"])]
    public function customPrompt(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true)['content'] ?? null;
        if (!$content) {
            return $this->json(['error' => 'Message content is required'], 400);
        }
        $response = $this->groqApiService->query($content);
        return $this->json($response);
    }
}

