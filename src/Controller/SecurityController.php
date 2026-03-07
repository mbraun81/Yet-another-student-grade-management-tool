<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Lehrer;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/login_check', name: 'app_login_check')]
    public function loginCheck(): never
    {
        throw new \LogicException('This route is handled by the firewall.');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('This route is handled by the firewall.');
    }

    #[Route('/2fa/setup', name: 'app_2fa_setup')]
    public function totpSetup(TotpAuthenticatorInterface $totpAuthenticator): Response
    {
        /** @var Lehrer $lehrer */
        $lehrer = $this->getUser();

        if (!$lehrer instanceof Lehrer) {
            return $this->redirectToRoute('app_login');
        }

        if ($lehrer->isTotpEnabled()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $qrContent = $totpAuthenticator->getQRContent($lehrer);

        return $this->render('security/2fa_totp_setup.html.twig', [
            'qr_content' => $qrContent,
            'totp_secret' => $lehrer->getTotpSecret(),
        ]);
    }

    #[Route('/2fa/setup/confirm', name: 'app_2fa_setup_confirm', methods: ['POST'])]
    public function totpSetupConfirm(
        Request $request,
        TotpAuthenticatorInterface $totpAuthenticator,
        EntityManagerInterface $em,
    ): Response {
        /** @var Lehrer $lehrer */
        $lehrer = $this->getUser();

        if (!$lehrer instanceof Lehrer) {
            return $this->redirectToRoute('app_login');
        }

        if ($lehrer->isTotpEnabled()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $code = (string) $request->request->get('totp_code', '');

        if ($totpAuthenticator->checkCode($lehrer, $code)) {
            $lehrer->setTotpEnabled(true);
            $em->flush();

            $this->addFlash('success', 'Zwei-Faktor-Authentifizierung erfolgreich eingerichtet.');

            return $this->redirectToRoute('app_login');
        }

        $this->addFlash('error', 'Ungültiger Code. Bitte erneut versuchen.');

        return $this->redirectToRoute('app_2fa_setup');
    }
}
