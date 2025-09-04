<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Repository\UserRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use App\Entity\User;

class UserRepositoryUnsupportedTest extends KernelTestCase
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

    public function testUpgradePasswordThrowsForUnsupportedUser(): void
    {
        $other = new class implements PasswordAuthenticatedUserInterface {
            public function getPassword(): ?string { return 'x'; }
            public function eraseCredentials(): void {}
        };
        $this->expectException(UnsupportedUserException::class);
        $this->repo->upgradePassword($other, 'hash');
    }
}
