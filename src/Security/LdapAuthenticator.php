<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Lehrer;
use App\Repository\LehrerRepository;
use Doctrine\ORM\EntityManagerInterface;
use ParagonIE\ConstantTime\Base32;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class LdapAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly LdapAuthServiceInterface $ldapAuthService,
        private readonly LehrerRepository $lehrerRepository,
        private readonly EntityManagerInterface $em,
        private readonly RouterInterface $router,
    ) {
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }

    public function supports(Request $request): ?bool
    {
        return $request->isMethod('POST') && $request->getPathInfo() === '/login_check';
    }

    public function authenticate(Request $request): Passport
    {
        $username = trim((string) $request->request->get('username', ''));
        $password = (string) $request->request->get('password', '');
        $csrfToken = (string) $request->request->get('_csrf_token', '');

        // Validates credentials against LDAP; throws AuthenticationException on failure
        $this->ldapAuthService->authenticate($username, $password);

        return new SelfValidatingPassport(
            new UserBadge($username, function (string $identifier): Lehrer {
                $lehrer = $this->lehrerRepository->findByLdapUsername($identifier);

                if (null === $lehrer) {
                    $lehrer = new Lehrer();
                    $lehrer->setLdapUsername($identifier);
                    $lehrer->setTotpSecret(Base32::encodeUpperUnpadded(random_bytes(32)));
                    $this->em->persist($lehrer);
                    $this->em->flush();
                }

                return $lehrer;
            }),
            [new CsrfTokenBadge('authenticate', $csrfToken)],
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();

        if ($user instanceof Lehrer && !$user->isTotpEnabled()) {
            return new RedirectResponse($this->router->generate('app_2fa_setup'));
        }

        // scheb/2fa v8 creates the TwoFactorToken before onAuthenticationSuccess (via CheckPassportEvent)
        // but does not set a redirect on LoginSuccessEvent. We redirect manually so the controller is never reached.
        return new RedirectResponse($this->router->generate('2fa_login'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse($this->router->generate('app_login'));
    }
}
