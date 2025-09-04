<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

class SecurityControllerTest extends WebTestCase
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new Kernel('test', true);
    }
    public function testLoginPageRenders(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('login', strtolower($client->getResponse()->getContent()));
    }

    public function testLogoutRouteIsProtectedByFirewall(): void
    {
        $client = static::createClient();
        $client->request('GET', '/logout');
        // Symfony renverra en général un 302 vers /login ou la page précédente
        $this->assertTrue(in_array($client->getResponse()->getStatusCode(), [302, 303]), 'Logout should redirect');
    }
}
