<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

class SecurityControllerLogoutLogicTest extends WebTestCase
{
    protected static function createKernel(array $options = []): KernelInterface { return new Kernel('test', true); }

    public function testLogoutDirectCallThrowsLogicException(): void
    {
        $this->expectException(\LogicException::class);
        static::getContainer()->get('router')->getRouteCollection(); // warm
        // Appel direct du contrôleur pour couvrir la ligne d'exception
        $controller = new \App\Controller\SecurityController();
        $controller->logout();
    }
}
