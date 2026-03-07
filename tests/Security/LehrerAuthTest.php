<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\LdapAuthService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LehrerAuthTest extends TestCase
{
    private const SEARCH_DN = 'CN=admin,DC=linuxmuster,DC=lan';
    private const SEARCH_PW = 'admin_dev_password';
    private const TEACHERS_DN = 'OU=Teachers,OU=default-school,OU=SCHOOLS,DC=linuxmuster,DC=lan';
    private const LOGIN_ATTR = 'uid';

    private LdapAuthService $service;

    protected function setUp(): void
    {
        $host = $_ENV['LDAP_HOST'] ?? 'localhost';
        $port = (int) ($_ENV['LDAP_PORT'] ?? 389);

        $ldap = Ldap::create('ext_ldap', ['host' => $host, 'port' => $port]);
        
        try {
            $ldap->bind(self::SEARCH_DN, self::SEARCH_PW);
        } catch (\Throwable $e) {
            $this->markTestSkipped('LDAP-Server nicht erreichbar: ' . $e->getMessage());
        }

        $this->service = new LdapAuthService(
            $ldap,
            self::SEARCH_DN,
            self::SEARCH_PW,
            self::TEACHERS_DN,
            self::LOGIN_ATTR,
        );
    }

    #[Test]
    public function authenticateSucceedsWithValidCredentials(): void
    {
        $result = $this->service->authenticate('mueller', 'lehrer2024');
        $this->assertSame('mueller', $result);
    }

    #[Test]
    public function authenticateSucceedsForAllTeachers(): void
    {
        $this->assertSame('schmidt', $this->service->authenticate('schmidt', 'lehrer2024'));
        $this->assertSame('fischer', $this->service->authenticate('fischer', 'lehrer2024'));
    }

    #[Test]
    public function authenticateThrowsWhenPasswordIsWrong(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->service->authenticate('mueller', 'falsch');
    }

    #[Test]
    public function authenticateThrowsWhenUserNotFound(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->service->authenticate('nichtvorhanden', 'x');
    }

    #[Test]
    public function authenticateThrowsForStudent(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->service->authenticate('schueler1', 'schueler2024');
    }
}
