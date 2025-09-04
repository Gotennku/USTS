<?php

declare(strict_types=1);

namespace App\Tests\Stub\Stripe;

use Stripe\StripeClient;

class StripeStubClient extends StripeClient
{
    public object $checkout;
    public object $billingPortal;
    public object $customers;

    public function __construct()
    {
        // parent constructor not needed; we just mimic structure
        $this->checkout = new class {
            public object $sessions; public function __construct(){ $this->sessions = new class { public function create(array $p){ return (object)['url' => 'https://stripe/session/test']; } }; }
        };
        $this->billingPortal = new class {
            public object $sessions; public function __construct(){ $this->sessions = new class { public function create(array $p){ return (object)['url' => 'https://stripe/portal/session/test']; } }; }
        };
        $this->customers = new class {
            public function create(array $p){ return (object)['id' => 'cus_'.substr(md5(json_encode($p)),0,8)]; }
        };
    }
}
