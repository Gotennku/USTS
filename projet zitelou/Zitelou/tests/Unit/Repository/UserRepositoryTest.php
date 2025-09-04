<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    protected static function createKernel(array $options = []): \Symfony\Component\HttpKernel\KernelInterface { return new \App\Kernel('test', true); }

    private UserRepository $repo;

    protected function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get('doctrine')->getManager();
        $tool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $tool->dropDatabase();
        $tool->createSchema($metadata);
        $this->repo = $em->getRepository(User::class);
    }

    public function testUpgradePassword(): void
    {
        $user = (new User())->setEmail('upgrade@example.test')->setPassword('old');
        $em = self::getContainer()->get('doctrine')->getManager();
        $em->persist($user); $em->flush();
        $this->repo->upgradePassword($user, 'newHash');
        $this->assertSame('newHash', $user->getPassword());
    }
}
