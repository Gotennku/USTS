<?php

declare(strict_types=1);

namespace App\Tests\Unit\StripeIntegration;

use App\Entity\User;
use App\Stripe\StripeClientFactory;
use App\StripeIntegration\Adapter\DoctrineCustomerProvider;
use Doctrine\ORM\Tools\SchemaTool;
use App\Tests\Stub\Stripe\StripeStubClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineCustomerProviderTest extends KernelTestCase
{
    protected static function createKernel(array $options = []): \Symfony\Component\HttpKernel\KernelInterface { return new \App\Kernel('test', true); }

    private function setupSchema(): void
    {
        $em = self::getContainer()->get('doctrine')->getManager();
        $tool = new SchemaTool($em);
        $meta = $em->getMetadataFactory()->getAllMetadata();
        $tool->dropDatabase();
        $tool->createSchema($meta);
    }

    public function testEnsureCustomerCreatesRemoteAndSavesId(): void
    {
        self::bootKernel();
        $this->setupSchema();
        $em = self::getContainer()->get('doctrine')->getManager();

        // Fake factory renvoyant un client Stripe valide avec un service customers custom
    $stripeClient = new StripeStubClient();
    $factory = new class($stripeClient) extends StripeClientFactory { public function __construct(private StripeStubClient $client) {} public function create(): \Stripe\StripeClient { return $this->client; } public function getWebhookSecret(): ?string { return null; }};

        $provider = new DoctrineCustomerProvider($em, $factory);
        $user = (new User())->setEmail('cust@example.test')->setPassword('x');
        $em->persist($user); $em->flush();
        $id = $provider->ensureCustomer((string)$user->getId(), 'cust@example.test');
        $this->assertNotEmpty($id);
        $em->refresh($user);
        $this->assertSame($id, $user->getStripeCustomerId());

        // Deuxième appel doit retourner même id sans recréer
        $again = $provider->ensureCustomer((string)$user->getId(), 'cust@example.test');
        $this->assertSame($id, $again);
    }
}
