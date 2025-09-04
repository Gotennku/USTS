<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use App\Kernel;

class SecurityControllerAuthenticatedTest extends WebTestCase
{
    protected static function createKernel(array $options = []): KernelInterface { return new Kernel('test', true); }

    public function testLoginWhenAuthenticatedRedirectsToHome(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user = (new User())->setEmail('auth_unique@example.test')->setPassword('x');
        $em->persist($user); $em->flush();
        // Utilise l'aide loginUser pour authentifier via firewall main
        $client->loginUser($user, 'main');
        $client->request('GET', '/login');
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect('/'));
    }
}
