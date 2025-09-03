<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class LoginController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function __invoke(): JsonResponse
    {
        // La sécurité intercepte avant; si on arrive ici c'est que quelque chose n'a pas géré la réponse
        return new JsonResponse(['message' => 'JSON login route'], 200);
    }
}
