<?php

namespace App\Tests\Api;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

abstract class DatabaseWebTestCase extends WebTestCase
{
    protected static bool $schemaReady = false;
    protected EntityManagerInterface $em;
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        if (!self::$schemaReady) {
            $em = static::getContainer()->get(EntityManagerInterface::class);
            $tool = new SchemaTool($em);
            $meta = $em->getMetadataFactory()->getAllMetadata();
            $tool->dropDatabase();
            if ($meta) {
                $tool->createSchema($meta);
            }
            self::$schemaReady = true;
        }
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }
}