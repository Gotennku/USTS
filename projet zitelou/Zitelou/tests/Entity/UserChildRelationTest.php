<?php

namespace App\Tests\Entity;

use App\Entity\Child;
use App\Entity\User;

class UserChildRelationTest extends DatabaseTestCase
{
    public function testAddAndRemoveChildMaintainsBidirectionalRelation(): void
    {
        $user = EntityFactory::user();
        $child = EntityFactory::child($user);
        $user->addChild($child);

        $this->em->persist($user);
        $this->em->persist($child);
        $this->em->flush();
        $id = $child->getId();
        $this->em->clear();

        $reloaded = $this->em->getRepository(Child::class)->find($id);
        self::assertNotNull($reloaded);
        self::assertInstanceOf(User::class, $reloaded->getParent());
        self::assertSame($reloaded->getParent()->getId(), $reloaded->getParent()->getId());

        $parent = $reloaded->getParent();
        self::assertCount(1, $parent->getChildren());

        // Remove and assert
        $parent->removeChild($reloaded);
        self::assertCount(0, $parent->getChildren());
    }
}
