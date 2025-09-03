<?php

namespace App\Tests\Entity;

use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Throwable;

abstract class DatabaseTestCase extends KernelTestCase
{
    protected EntityManagerInterface $em;
    private static bool $schemaCreated = false;

    protected static function createKernel(array $options = []): \Symfony\Component\HttpKernel\KernelInterface
    {
        return new Kernel('test', true);
    }

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var EntityManagerInterface $em */
        $this->em = self::getContainer()->get('doctrine')->getManager();

        if (!self::$schemaCreated) {
            $metadata = $this->em->getMetadataFactory()->getAllMetadata();
            $tool = new SchemaTool($this->em);
            try {
                $tool->dropSchema($metadata);
            } catch (Throwable) {
                // ignore
            }
            $tool->createSchema($metadata);
            self::$schemaCreated = true;
        } else {
            // Clean tables between tests for isolation (truncate in FK-safe order)
            $conn = $this->em->getConnection();
            $platform = $conn->getDatabasePlatform();
            $isSqlite = str_contains(strtolower($platform->getName()), 'sqlite');
            if ($isSqlite) {
                $conn->executeStatement('PRAGMA foreign_keys = OFF');
            } else {
                $conn->executeStatement('SET FOREIGN_KEY_CHECKS=0');
            }
            foreach ($this->em->getMetadataFactory()->getAllMetadata() as $meta) {
                $sql = $platform->getTruncateTableSQL($meta->getTableName());
                $conn->executeStatement($sql);
            }
            if ($isSqlite) {
                $conn->executeStatement('PRAGMA foreign_keys = ON');
            } else {
                $conn->executeStatement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
    }

    protected function tearDown(): void
    {
        $this->em->clear();
        parent::tearDown();
    }
}
