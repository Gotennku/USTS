<?php

namespace App\Controller;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Rediriger si déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home'); // Ajuster la route cible
        }

        // Récupérer une éventuelle erreur d'authentification
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier identifiant (email) saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Géré automatiquement par Symfony (firewall + security.yaml)
        throw new LogicException('Ce code ne doit jamais être exécuté.');
    }
}
