<?php

namespace App\Tests\Entity;

use App\Entity\{AuthorizedContact, AuthorizedApp, FeatureAccess, EmergencyContact, EmergencyCall, GeoLocation, Child};
use App\Enum\EmergencyCallStatus;

class ChildAncillaryRelationsTest extends DatabaseTestCase
{
    public function testAncillaryCollections(): void
    {
        $user = EntityFactory::user();
        $child = EntityFactory::child($user);
        $entities = [
            EntityFactory::authorizedContact($child, 1),
            EntityFactory::authorizedApp($child, 1),
            EntityFactory::featureAccess($child, 'gps'),
            EntityFactory::emergencyContact($child, 1),
            EntityFactory::emergencyCall($child, EmergencyCallStatus::SUCCESS),
            EntityFactory::geoLocation($child, 10.0, 20.0),
        ];

        $this->em->persist($user);
        $this->em->persist($child);
        foreach ($entities as $e) { $this->em->persist($e); }
        $this->em->flush();
        $id = $child->getId();
        $this->em->clear();

        $reloaded = $this->em->getRepository(Child::class)->find($id);
        self::assertNotNull($reloaded);
        self::assertCount(1, $reloaded->getAuthorizedContacts());
        self::assertCount(1, $reloaded->getAuthorizedApps());
        self::assertCount(1, $reloaded->getFeatureAccesses());
        self::assertCount(1, $reloaded->getEmergencyContacts());
        self::assertCount(1, $reloaded->getEmergencyCalls());
        self::assertCount(1, $reloaded->getGeoLocations());
    }
}
