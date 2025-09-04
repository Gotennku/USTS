<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route(path: '/admin', name: 'admin_dashboard')]
    public function __invoke(): Response
    {
        return new Response('ADMIN DASHBOARD');
    }
}
