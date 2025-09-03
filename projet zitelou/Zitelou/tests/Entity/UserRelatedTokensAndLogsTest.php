<?php

namespace App\Tests\Entity;

use App\Entity\{User, AuthToken, PasswordResetToken, UserLog, BanList, AdminLog, ParentalSettings};

class UserRelatedTokensAndLogsTest extends DatabaseTestCase
{
    public function testUserTokensLogsAndSettings(): void
    {
        $admin = EntityFactory::user(1);
        $user = EntityFactory::user(2);
        $auth = EntityFactory::authToken($user);
        $reset = EntityFactory::passwordResetToken($user);
        $log = EntityFactory::userLog($user);
        $ban = EntityFactory::banList($user);
        $adminLog = EntityFactory::adminLog($admin);
        $settings = EntityFactory::parentalSettings($user);

        foreach ([$admin,$user,$auth,$reset,$log,$ban,$adminLog,$settings] as $e) { $this->em->persist($e); }
        $this->em->flush();
        $id = $user->getId();
        $this->em->clear();

        /** @var User $reloaded */
        $reloaded = $this->em->getRepository(User::class)->find($id);
        self::assertNotNull($reloaded);
        self::assertCount(1, $reloaded->getAuthTokens());
        self::assertCount(1, $reloaded->getPasswordResetTokens());
        self::assertCount(1, $reloaded->getUserLogs());
        self::assertCount(1, $reloaded->getBanLists());
        self::assertNotNull($reloaded->getParentalSettings());
    }
}
