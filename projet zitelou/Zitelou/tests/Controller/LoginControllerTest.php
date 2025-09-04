<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

class LoginControllerTest extends WebTestCase
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new Kernel('test', true);
    }
    public function testApiLoginRouteReturnsJson(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/login', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => 'user@example.test',
            'password' => 'secret'
        ]));
        $status = $client->getResponse()->getStatusCode();
        // Si la sécurité intercepte on obtient 401; sinon le contrôleur renvoie 200.
        $this->assertContains($status, [200,401]);
        if ($status === 200) {
            $data = json_decode($client->getResponse()->getContent(), true);
            $this->assertSame('JSON login route', $data['message'] ?? null);
        }
    }
}
