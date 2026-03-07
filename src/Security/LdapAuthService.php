<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class LdapAuthService implements LdapAuthServiceInterface
{
    public function __construct(
        private readonly LdapInterface $ldap,
        private readonly string $searchDn,
        private readonly string $searchPassword,
        private readonly string $teachersDn,
        private readonly string $loginAttr,
    ) {
    }

    public function authenticate(string $username, string $password): string
    {
        try {
            $this->ldap->bind($this->searchDn, $this->searchPassword);
        } catch (ConnectionException $e) {
            throw new CustomUserMessageAuthenticationException('Verbindung zum LDAP-Server fehlgeschlagen.', [], 0, $e);
        }
        $escapedUsername = $this->ldap->escape($username, '', LdapInterface::ESCAPE_FILTER);
        $filter = sprintf('(&(%s=%s)(objectClass=person))', $this->loginAttr, $escapedUsername);
        $query = $this->ldap->query($this->teachersDn, $filter);
        $results = $query->execute();
        if (0 === count($results)) {
            throw new CustomUserMessageAuthenticationException('Benutzername oder Passwort falsch.');
        }

        $userDn = $results[0]->getDn();

        try {
            $this->ldap->bind($userDn, $password);
        } catch (ConnectionException $e) {
            // ConnectionException here means wrong password, not a network problem
            throw new CustomUserMessageAuthenticationException('Benutzername oder Passwort falsch.', [], 0, $e);
        }

        return $username;
    }
}
