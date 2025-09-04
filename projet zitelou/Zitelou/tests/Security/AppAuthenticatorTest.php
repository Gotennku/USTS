<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use App\Security\AppAuthenticator;

class AppAuthenticatorTest extends KernelTestCase
{
    protected static function createKernel(array $options = []): \Symfony\Component\HttpKernel\KernelInterface { return new \App\Kernel('test', true); }

    public function testOnAuthenticationSuccessRedirectsToTargetPath(): void
    {
    self::bootKernel();
    $container = self::getContainer();
    /** @var SessionInterface $session */
    $session = $container->get('session.factory')->createSession();
    $session->start();
    $session->set('_security.main.target_path', '/target-prot');
    $authenticator = $container->get(AppAuthenticator::class);
        $user = (new User())->setEmail('redir1@example.test')->setPassword('x');
        $token = new UsernamePasswordToken($user, 'main', ['ROLE_USER']);
    $request = Request::create('/login');
    $request->setSession($session);
    $response = $authenticator->onAuthenticationSuccess($request, $token, 'main');
        self::assertNotNull($response);
        self::assertTrue($response->isRedirect('/target-prot'));
    }

    public function testOnAuthenticationSuccessRedirectsAdmin(): void
    {
    self::bootKernel();
    $container = self::getContainer();
    $authenticator = $container->get(AppAuthenticator::class);
        $user = (new User())->setEmail('admin@example.test')->setPassword('x');
        // inject ROLE_ADMIN
        $refl = new \ReflectionProperty(User::class, 'roles');
        $refl->setAccessible(true);
        $refl->setValue($user, ['ROLE_USER','ROLE_ADMIN']);
        $token = new UsernamePasswordToken($user, 'main', ['ROLE_USER','ROLE_ADMIN']);
    $session = $container->get('session.factory')->createSession(); $session->start();
    $request = Request::create('/login'); $request->setSession($session);
    $response = $authenticator->onAuthenticationSuccess($request, $token, 'main');
        self::assertTrue($response->isRedirect());
    self::assertSame('/admin', $response->headers->get('Location'));
    }

    public function testOnAuthenticationSuccessRedirectsHome(): void
    {
    self::bootKernel();
    $container = self::getContainer();
    $authenticator = $container->get(AppAuthenticator::class);
        $user = (new User())->setEmail('home@example.test')->setPassword('x');
        $token = new UsernamePasswordToken($user, 'main', ['ROLE_USER']);
    $session = $container->get('session.factory')->createSession(); $session->start();
    $request = Request::create('/login'); $request->setSession($session);
    $response = $authenticator->onAuthenticationSuccess($request, $token, 'main');
        self::assertTrue($response->isRedirect());
        self::assertStringContainsString('/', $response->headers->get('Location'));
    }
}
