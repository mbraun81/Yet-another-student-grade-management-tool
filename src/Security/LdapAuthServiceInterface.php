<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

interface LdapAuthServiceInterface
{
    /**
     * Searches for the user in LDAP and verifies their password.
     * Returns the canonical username (value of LDAP_LOGIN_ATTR) on success.
     *
     * @throws AuthenticationException on invalid credentials or connection failure
     */
    public function authenticate(string $username, string $password): string;
}
