<?php

namespace App\Tests\Entity;

use App\Entity\{AdminConfig, AuditLog, BackOfficeStat};

class SimpleEntitiesPersistenceTest extends DatabaseTestCase
{
    public function testPersistSimpleEntities(): void
    {
        $cfg = EntityFactory::adminConfig();
        $audit = EntityFactory::auditLog();
        $stat = EntityFactory::backOfficeStat();

        foreach ([$cfg,$audit,$stat] as $e) { $this->em->persist($e); }
        $this->em->flush();
        self::assertNotNull($cfg->getId());
        self::assertNotNull($audit->getId());
        self::assertNotNull($stat->getId());
    }
}
