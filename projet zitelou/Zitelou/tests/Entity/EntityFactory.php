<?php

namespace App\Tests\Entity;

use App\Entity\{AdminConfig, AdminLog, AuditLog, AuthToken, AuthorizedApp, AuthorizedContact, BackOfficeStat, BanList, Child, EmergencyCall, EmergencyContact, FeatureAccess, GeoLocation, ParentalSettings, PasswordResetToken, Payment, StripeWebhookLog, Subscription, SubscriptionHistory, SubscriptionPlan, User, UserLog};
use App\Enum\{EmergencyCallStatus, PaymentStatus, SubscriptionEvent, SubscriptionStatus};
use DateTimeImmutable;

class EntityFactory
{
    public static function user(int $i = 1): User
    {
        $u = new User();
        $u->setEmail("user{$i}@example.test");
        $u->setPassword('hash');
        return $u;
    }

    public static function child(User $parent, int $i = 1): Child
    {
        $c = new Child();
        $c->setParent($parent);
        $c->setFirstname('Kid'.$i);
        $c->setAge(10 + $i);
        return $c;
    }

    public static function subscription(User $user, ?SubscriptionPlan $plan = null): Subscription
    {
        $s = new Subscription();
        $s->setUser($user);
        $s->setStatus(SubscriptionStatus::ACTIVE);
        $s->setStartDate(new DateTimeImmutable('-1 day'));
        if ($plan) {
            $s->setPlan($plan);
        }
        return $s;
    }

    public static function subscriptionPlan(int $i = 1): SubscriptionPlan
    {
        $p = new SubscriptionPlan();
        $p->setName('Plan '.$i)->setDurationDays(30)->setPrice('9.99')->setCurrency('EUR');
        return $p;
    }

    public static function payment(Subscription $s, int $i = 1): Payment
    {
        $p = new Payment();
        $p->setSubscription($s)->setAmount(9.99)->setCurrency('EUR')->setStatus(PaymentStatus::SUCCEEDED)->setStripePaymentIntentId('pi_'.$i);
        return $p;
    }

    public static function subscriptionHistory(Subscription $s, int $i = 1): SubscriptionHistory
    {
        $h = new SubscriptionHistory();
        $h->setSubscription($s)->setStatus(SubscriptionStatus::ACTIVE)->setEvent($i === 1 ? SubscriptionEvent::CREATED : SubscriptionEvent::RENEWED);
        return $h;
    }

    public static function stripeWebhookLog(Subscription $s, string $event = 'invoice.paid'): StripeWebhookLog
    {
        $l = new StripeWebhookLog();
                $l->setSubscription($s)
                    ->setEventType($event)
                    ->setEventId('evt_factory_'.uniqid())
                    ->setPayload(['test' => true])
                    ->setProcessed(false);
        return $l;
    }

    public static function featureAccess(Child $c, string $feature = 'gps'): FeatureAccess
    {
        $f = new FeatureAccess();
        $f->setChild($c)->setFeature($feature)->setEnabled(true);
        return $f;
    }

    public static function authorizedContact(Child $c, int $i = 1): AuthorizedContact
    {
        $a = new AuthorizedContact();
        $a->setChild($c)->setName('Contact'.$i)->setPhoneNumber('060000000'.$i)->setRelation('family');
        return $a;
    }

    public static function authorizedApp(Child $c, int $i = 1): AuthorizedApp
    {
        $a = new AuthorizedApp();
        $a->setChild($c)->setAppName('App'.$i)->setPackageName('pkg.app'.$i)->setIsAllowed(true);
        return $a;
    }

    public static function emergencyContact(Child $c, int $i = 1): EmergencyContact
    {
        $e = new EmergencyContact();
        $e->setChild($c)->setName('Emerg'.$i)->setPhoneNumber('070000000'.$i);
        return $e;
    }

    public static function emergencyCall(Child $c, EmergencyCallStatus $status = EmergencyCallStatus::SUCCESS): EmergencyCall
    {
        $e = new EmergencyCall();
        $e->setChild($c)->setStatus($status);
        return $e;
    }

    public static function geoLocation(Child $c, float $lat = 1.0, float $lng = 2.0): GeoLocation
    {
        $g = new GeoLocation();
        $g->setChild($c)->setLatitude($lat)->setLongitude($lng);
        return $g;
    }

    public static function authToken(User $u, int $i = 1): AuthToken
    {
        $t = new AuthToken();
        $t->setUser($u)->setToken('auth_'.$i)->setExpiresAt(new DateTimeImmutable('+1 hour'));
        return $t;
    }

    public static function passwordResetToken(User $u, int $i = 1): PasswordResetToken
    {
        $t = new PasswordResetToken();
        $t->setUser($u)->setToken('reset_'.$i)->setExpiresAt(new DateTimeImmutable('+1 hour'));
        return $t;
    }

    public static function userLog(User $u, int $i = 1): UserLog
    {
        $l = new UserLog();
        $l->setUser($u)->setIpAddress('127.0.0.' . $i)->setDevice('device'.$i)->setAction('LOGIN');
        return $l;
    }

    public static function banList(User $u, int $i = 1): BanList
    {
        $b = new BanList();
        $b->setUser($u)->setReason('Reason'.$i)->setBannedUntil(new DateTimeImmutable('+1 day'));
        return $b;
    }

    public static function adminLog(User $admin, int $i = 1): AdminLog
    {
        $l = new AdminLog();
        $l->setAdmin($admin)->setAction('ACT'.$i)->setTarget('Target'.$i);
        return $l;
    }

    public static function parentalSettings(User $u): ParentalSettings
    {
        $p = new ParentalSettings();
        $p->setUser($u)->setPinCode('1234')->setSafeMode(true);
        return $p;
    }

    public static function adminConfig(int $i = 1): AdminConfig
    {
        $c = new AdminConfig();
        $c->setKey('k'.$i)->setValue('v'.$i);
        return $c;
    }

    public static function auditLog(int $i = 1): AuditLog
    {
        $a = new AuditLog();
        $a->setAction('ACTION'.$i)->setActor('actor'.$i);
        return $a;
    }

    public static function backOfficeStat(string $metric = 'users', float $value = 10): BackOfficeStat
    {
        $b = new BackOfficeStat();
        $b->setMetric($metric)->setValue($value);
        return $b;
    }
}
