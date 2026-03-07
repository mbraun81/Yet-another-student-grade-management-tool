<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\Lehrer;
use App\Repository\LehrerRepository;
use App\Security\LdapAuthenticator;
use App\Security\LdapAuthServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class LdapAuthenticatorTest extends TestCase
{
    private LdapAuthServiceInterface&MockObject $ldapAuthService;
    private LehrerRepository&MockObject $lehrerRepository;
    private EntityManagerInterface&MockObject $em;
    private RouterInterface&MockObject $router;
    private LdapAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->ldapAuthService = $this->createMock(LdapAuthServiceInterface::class);
        $this->lehrerRepository = $this->createMock(LehrerRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->router = $this->createMock(RouterInterface::class);

        $this->authenticator = new LdapAuthenticator(
            $this->ldapAuthService,
            $this->lehrerRepository,
            $this->em,
            $this->router,
        );
    }

    // ── supports() ────────────────────────────────────────────────────────────

    #[Test]
    public function supportsPostToLoginCheck(): void
    {
        $request = $this->makeRequest('POST', '/login_check');
        $this->assertTrue($this->authenticator->supports($request));
    }

    #[Test]
    public function doesNotSupportGetToLoginCheck(): void
    {
        $request = $this->makeRequest('GET', '/login_check');
        $this->assertFalse($this->authenticator->supports($request));
    }

    #[Test]
    public function doesNotSupportOtherPaths(): void
    {
        $request = $this->makeRequest('POST', '/login');
        $this->assertFalse($this->authenticator->supports($request));
    }

    // ── authenticate() ────────────────────────────────────────────────────────

    #[Test]
    public function authenticateReturnsPassportOnSuccess(): void
    {
        $this->ldapAuthService->expects($this->once())
            ->method('authenticate')
            ->with('mueller', 'lehrer2024');

        $request = $this->makeLoginRequest('mueller', 'lehrer2024');
        $passport = $this->authenticator->authenticate($request);

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);
        $this->assertSame('mueller', $passport->getBadge(\Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge::class)->getUserIdentifier());
    }

    #[Test]
    public function authenticatePropagatesLdapException(): void
    {
        $this->ldapAuthService->method('authenticate')
            ->willThrowException(new CustomUserMessageAuthenticationException('Benutzername oder Passwort falsch.'));

        $request = $this->makeLoginRequest('mueller', 'wrongpassword');

        $this->expectException(AuthenticationException::class);
        $this->authenticator->authenticate($request);
    }

    #[Test]
    public function authenticateTrimsUsername(): void
    {
        $this->ldapAuthService->expects($this->once())
            ->method('authenticate')
            ->with('mueller', 'lehrer2024');

        $request = $this->makeLoginRequest('  mueller  ', 'lehrer2024');
        $passport = $this->authenticator->authenticate($request);

        $this->assertSame('mueller', $passport->getBadge(\Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge::class)->getUserIdentifier());
    }

    // ── onAuthenticationSuccess() ─────────────────────────────────────────────

    #[Test]
    public function onSuccessRedirectsToTotpSetupForNewUser(): void
    {
        $this->router->method('generate')
            ->with('app_2fa_setup')
            ->willReturn('/2fa/setup');

        $lehrer = new Lehrer();
        $lehrer->setLdapUsername('mueller');
        // totpEnabled defaults to false

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($lehrer);

        $response = $this->authenticator->onAuthenticationSuccess(
            $this->createMock(Request::class),
            $token,
            'main',
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/2fa/setup', $response->getTargetUrl());
    }

    #[Test]
    public function onSuccessReturnsNullForUserWithTotpEnabled(): void
    {
        $lehrer = new Lehrer();
        $lehrer->setLdapUsername('mueller');
        $lehrer->setTotpSecret('SOMESECRET');
        $lehrer->setTotpEnabled(true);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($lehrer);

        $response = $this->authenticator->onAuthenticationSuccess(
            $this->createMock(Request::class),
            $token,
            'main',
        );

        $this->assertNull($response);
    }

    // ── onAuthenticationFailure() ─────────────────────────────────────────────

    #[Test]
    public function onFailureStoresErrorAndRedirectsToLogin(): void
    {
        $this->router->method('generate')
            ->with('app_login')
            ->willReturn('/login');

        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())
            ->method('set')
            ->with(\Symfony\Component\Security\Http\SecurityRequestAttributes::AUTHENTICATION_ERROR);

        $request = $this->createMock(Request::class);
        $request->method('getSession')->willReturn($session);

        $response = $this->authenticator->onAuthenticationFailure(
            $request,
            new CustomUserMessageAuthenticationException('Benutzername oder Passwort falsch.'),
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/login', $response->getTargetUrl());
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeRequest(string $method, string $path): Request
    {
        $request = $this->createMock(Request::class);
        $request->method('isMethod')
            ->with('POST')
            ->willReturn($method === 'POST');
        $request->method('getPathInfo')->willReturn($path);

        return $request;
    }

    private function makeLoginRequest(string $username, string $password): Request
    {
        $request = $this->createMock(Request::class);
        $request->method('isMethod')->with('POST')->willReturn(true);
        $request->method('getPathInfo')->willReturn('/login_check');

        $bag = new InputBag([
            'username' => $username,
            'password' => $password,
            '_csrf_token' => 'test-token',
        ]);
        $request->request = $bag;

        return $request;
    }
}
