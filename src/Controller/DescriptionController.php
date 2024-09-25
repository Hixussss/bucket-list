<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;



//
//          TODO : Optimiser la fonction pour un délait relativemenbt rapide traitement rapide car saisi en temps réel
//          Penser à lui donner un prompt précis pour qu'il puisse générer une description pertinente
//          Utiliser un model comme neo-gpt (2.7B) pour générer la description
//          Passer par huggingface donc récupérer une clé API
//

class DescriptionController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/generate-description', name: 'generate_description', methods: ['POST'])]
    public function generateDescription(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $title = $data['title'] ?? '';

        if (empty($title)) {
            return new JsonResponse(['error' => 'Title is required'], 400);
        }

        $response = $this->httpClient->request('POST', 'https://api-inference.huggingface.co/models/EleutherAI/gpt-neo-2.7B', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getParameter('HUGGINGFACE_API_KEY'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'inputs' => 'Generate a description for the following title: ' . $title,
            ],
        ]);

        $content = $response->toArray();
        $description = $content[0]['generated_text'] ?? 'No description generated';

        return new JsonResponse(['description' => $description]);
    }
}