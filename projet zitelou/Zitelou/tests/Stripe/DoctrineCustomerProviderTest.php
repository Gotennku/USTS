<?php

declare(strict_types=1);

namespace App\Tests\Stripe;

use App\Entity\User;
use App\StripeIntegration\Adapter\DoctrineCustomerProvider;
use App\Stripe\StripeClientFactory;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineCustomerProviderTest extends KernelTestCase
{
    protected static function createKernel(array $options = []): \Symfony\Component\HttpKernel\KernelInterface { return new \App\Kernel('test', true); }

    private \Doctrine\ORM\EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $tool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropDatabase();
        $tool->createSchema($metadata);
    }

    public function testEnsureCustomerCreatesAndPersistsId(): void
    {
        $user = (new User())->setEmail('cust1@example.test')->setPassword('x');
        $this->em->persist($user); $this->em->flush();

        // Fake stripe client & factory
        // Stub StripeClient minimal respectant le type
        $stripeClient = new class('sk_test') extends \Stripe\StripeClient {
            public object $customers;
            public function __construct(string $key) { \Stripe\Stripe::setApiKey($key); $this->customers = new class { public function create(array $data) { return (object)['id' => 'cus_test_123']; } }; }
            public function __get($name) { if ($name === 'customers') { return $this->customers; } return parent::__get($name); }
        };
        $factory = new class($stripeClient) extends StripeClientFactory {
            public function __construct(private \Stripe\StripeClient $client) {}
            public function create(): \Stripe\StripeClient { return $this->client; }
        };
        self::getContainer()->set(StripeClientFactory::class, $factory);

        $provider = self::getContainer()->get(DoctrineCustomerProvider::class);
        $id = $provider->ensureCustomer((string)$user->getId(), $user->getEmail());
        self::assertSame('cus_test_123', $id);
        self::assertSame('cus_test_123', $user->getStripeCustomerId());

        // Idempotent second call
        $second = $provider->ensureCustomer((string)$user->getId(), $user->getEmail());
        self::assertSame('cus_test_123', $second);
    }

    public function testEnsureCustomerThrowsIfUserMissing(): void
    {
        $this->expectException(\RuntimeException::class);
        $provider = self::getContainer()->get(DoctrineCustomerProvider::class);
        $provider->ensureCustomer('999999', 'x@example.test');
    }
}
