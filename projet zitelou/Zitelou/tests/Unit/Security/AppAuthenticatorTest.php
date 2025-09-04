<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Security\AppAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class AppAuthenticatorTest extends TestCase
{
    private function createAuthenticator(): AppAuthenticator
    {
        $urlGen = new class implements UrlGeneratorInterface {
            public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string { return '/' . $name; }
            public function setContext(RequestContext $context): void {}
            public function getContext(): RequestContext { return new RequestContext(); }
        };
        return new AppAuthenticator($urlGen);
    }

    public function testAuthenticateBuildsExpectedPassport(): void
    {
        $authenticator = $this->createAuthenticator();
        $request = Request::create('/login', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'email' => 'user@example.test',
            'password' => 'secret',
            '_csrf_token' => 'abc'
        ]));
        $request->setSession(new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage()));

        $passport = $authenticator->authenticate($request);

        $this->assertInstanceOf(UserBadge::class, $passport->getBadge(UserBadge::class));
        $this->assertInstanceOf(CsrfTokenBadge::class, $passport->getBadge(CsrfTokenBadge::class));
        $this->assertInstanceOf(RememberMeBadge::class, $passport->getBadge(RememberMeBadge::class));
        $this->assertSame('user@example.test', $request->getSession()->get(SecurityRequestAttributes::LAST_USERNAME));
    }

    private function token(array $roles): TokenInterface
    {
        return new class($roles) implements TokenInterface {
            public function __construct(private array $roles) {}
            public function __toString(): string { return ''; }
            public function getRoleNames(): array { return $this->roles; }
            // BC proxy
            public function getRoles(): array { return $this->getRoleNames(); }
            public function getCredentials(): mixed { return null; }
            public function getUser(): ?UserInterface {
                $roles = $this->roles;
                return new class($roles) implements UserInterface {
                    public function __construct(private array $roles) {}
                    public function getRoles(): array { return $this->roles; }
                    public function eraseCredentials(): void {}
                    public function getPassword(): ?string { return null; }
                    public function getUserIdentifier(): string { return 'id'; }
                };
            }
            public function setUser(UserInterface $user): void {}
            public function getUserIdentifier(): string { return 'id'; }
            public function isAuthenticated(): bool { return true; }
            public function setAuthenticated(bool $isAuthenticated): void {}
            public function eraseCredentials(): void {}
            public function getAttributes(): array { return []; }
            public function setAttributes(array $attributes): void {}
            public function hasAttribute(string $name): bool { return false; }
            public function getAttribute(string $name): mixed { return null; }
            public function setAttribute(string $name, mixed $value): void {}
            public function getFirewallName(): string { return 'main'; }
            public function isRememberMe(): bool { return false; }
            public function hasBeenUpgraded(): bool { return false; }
            public function __serialize(): array { return []; }
            public function __unserialize(array $data): void {}
        };
    }

    public function testOnAuthenticationSuccessRedirectsAdmin(): void
    {
    $authenticator = $this->createAuthenticator();
    $request = Request::create('/login');
    $request->setSession(new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage()));
    $response = $authenticator->onAuthenticationSuccess($request, $this->token(['ROLE_ADMIN']), 'main');
    // Comme la route admin_dashboard n'existe pas dans le test isolé, on vérifie simplement le pattern attendu
    $this->assertSame('/admin_dashboard', $response->headers->get('Location'));
    }

    public function testOnAuthenticationSuccessRedirectsUser(): void
    {
        $authenticator = $this->createAuthenticator();
        $request = Request::create('/login');
        $request->setSession(new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage()));
    $response = $authenticator->onAuthenticationSuccess($request, $this->token(['ROLE_USER']), 'main');
        $this->assertSame('/app_home', $response->headers->get('Location'));
    }

    public function testOnAuthenticationSuccessRedirectsToStoredTargetPath(): void
    {
        $authenticator = $this->createAuthenticator();
        $request = Request::create('/login');
        $session = new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage());
        $session->set('_security.main.target_path', '/protected-area');
        $request->setSession($session);
        $response = $authenticator->onAuthenticationSuccess($request, $this->token(['ROLE_USER']), 'main');
        $this->assertSame('/protected-area', $response->headers->get('Location'));
    }

    public function testGetLoginUrl(): void
    {
        $authenticator = $this->createAuthenticator();
        $ref = new \ReflectionClass($authenticator);
        $m = $ref->getMethod('getLoginUrl');
        $m->setAccessible(true);
        $url = $m->invoke($authenticator, Request::create('/login'));
        $this->assertSame('/app_login', $url);
    }
}
