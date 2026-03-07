<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\LdapAuthService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Ldap\Adapter\CollectionInterface;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LdapAuthServiceTest extends TestCase
{
    private const SEARCH_DN = 'CN=admin,DC=linuxmuster,DC=lan';
    private const SEARCH_PW = 'admin_dev_password';
    private const TEACHERS_DN = 'OU=Teachers,OU=SCHOOLS,DC=linuxmuster,DC=lan';
    private const LOGIN_ATTR = 'uid';

    private LdapInterface&MockObject $ldap;
    private LdapAuthService $service;

    protected function setUp(): void
    {
        $this->ldap = $this->createMock(LdapInterface::class);
        $this->service = new LdapAuthService(
            $this->ldap,
            self::SEARCH_DN,
            self::SEARCH_PW,
            self::TEACHERS_DN,
            self::LOGIN_ATTR,
        );
    }

    #[Test]
    public function authenticateSucceedsWithValidCredentials(): void
    {
        $userDn = 'uid=mueller,' . self::TEACHERS_DN;

        $this->ldap->expects($this->exactly(2))
            ->method('bind')
            ->with($this->callback(fn ($dn) => in_array($dn, [self::SEARCH_DN, $userDn], true)));

        $this->ldap->method('escape')
            ->with('mueller', '', LdapInterface::ESCAPE_FILTER)
            ->willReturn('mueller');

        $query = $this->createMock(QueryInterface::class);
        $query->method('execute')->willReturn($this->makeResults([$userDn]));

        $this->ldap->method('query')
            ->with(self::TEACHERS_DN, '(&(uid=mueller)(objectClass=person))')
            ->willReturn($query);

        $result = $this->service->authenticate('mueller', 'lehrer2024');

        $this->assertSame('mueller', $result);
    }

    #[Test]
    public function authenticateThrowsWhenSearchBindFails(): void
    {
        $this->ldap->method('bind')
            ->willThrowException(new ConnectionException('Cannot connect'));

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Verbindung zum LDAP-Server fehlgeschlagen.');

        $this->service->authenticate('mueller', 'lehrer2024');
    }

    #[Test]
    public function authenticateThrowsWhenUserNotFound(): void
    {
        $this->ldap->method('bind')
            ->with(self::SEARCH_DN, self::SEARCH_PW);

        $this->ldap->method('escape')->willReturn('unknown');

        $query = $this->createMock(QueryInterface::class);
        $query->method('execute')->willReturn($this->makeResults([]));

        $this->ldap->method('query')->willReturn($query);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Benutzername oder Passwort falsch.');

        $this->service->authenticate('unknown', 'lehrer2024');
    }

    #[Test]
    public function authenticateThrowsWhenPasswordIsWrong(): void
    {
        $userDn = 'uid=mueller,' . self::TEACHERS_DN;

        $this->ldap->expects($this->exactly(2))
            ->method('bind')
            ->willReturnCallback(function (string $dn, string $password) use ($userDn): void {
                if ($dn === $userDn) {
                    throw new ConnectionException('Invalid credentials');
                }
                // Search bind succeeds
            });

        $this->ldap->method('escape')->willReturn('mueller');

        $query = $this->createMock(QueryInterface::class);
        $query->method('execute')->willReturn($this->makeResults([$userDn]));

        $this->ldap->method('query')->willReturn($query);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Benutzername oder Passwort falsch.');

        $this->service->authenticate('mueller', 'wrongpassword');
    }

    #[Test]
    public function authenticateEscapesUsernameInLdapFilter(): void
    {
        $this->ldap->method('bind');
        $this->ldap->expects($this->once())
            ->method('escape')
            ->with('user*with(special)chars', '', LdapInterface::ESCAPE_FILTER)
            ->willReturn('user\2awith\28special\29chars');

        $query = $this->createMock(QueryInterface::class);
        $query->method('execute')->willReturn($this->makeResults([]));

        $this->ldap->expects($this->once())
            ->method('query')
            ->with(
                self::TEACHERS_DN,
                '(&(uid=user\2awith\28special\29chars)(objectClass=person))',
            )
            ->willReturn($query);

        $this->expectException(AuthenticationException::class);

        $this->service->authenticate('user*with(special)chars', 'pass');
    }

    // -------------------------------------------------------------------------

    /** @param string[] $dns */
    private function makeResults(array $dns): CollectionInterface
    {
        $entries = array_map(fn (string $dn) => new Entry($dn), $dns);

        $collection = $this->createMock(CollectionInterface::class);
        $collection->method('count')->willReturn(count($entries));
        $collection->method('offsetGet')->willReturnCallback(fn ($i) => $entries[$i]);
        $collection->method('toArray')->willReturn($entries);

        return $collection;
    }
}
