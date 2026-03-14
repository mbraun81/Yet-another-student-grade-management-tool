<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;
use App\Service\LdapImportService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/ldap/import/', name: 'ldap_import')]
final class LdapImportController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LdapImportService $ldapImport,
    ){}
    
    
    #[Route('index/', name: 'ldap_import_index')]
    public function index(): Response
    {
        return $this->render('ldap_import/index.html.twig', [
            'controller_name' => 'LdapImportController',
        ]);
    }

    #[Route('faecher/', name: 'ldap_import_faecher')]
    public function faecher(Request $request): Response
    {
        $this->logger->debug("importFromLdap");
        $this->ldapImport->importFaecher('ou=Projects,ou=default-school,ou=SCHOOLS,dc=linuxmuster,dc=lan');
        return $this->redirect($request->headers->get('referer'));
    }
    
    #[Route('klasse/', name: 'ldap_import_klasse')]
    public function klasse(Request $request): Response
    {
        $this->logger->debug("importFromLdap");
        $this->ldapImport->importKlassen('ou=Students,ou=default-school,ou=SCHOOLS,dc=linuxmuster,dc=lan');
        return $this->redirect($request->headers->get('referer'));
    }
    
    #[Route('lehrer/', name: 'ldap_import_lehrer')]
    public function lehrer(Request $request): Response
    {
        $this->logger->debug("importFromLdap");
        $this->ldapImport->importFaecher('ou=Projects,ou=default-school,ou=SCHOOLS,dc=linuxmuster,dc=lan');
        return $this->redirect($request->headers->get('referer'));
    }
    
    #[Route('schueler/', name: 'ldap_import_schueler')]
    public function schueler(Request $request): Response
    {
        $this->logger->debug("importFromLdap");
        $this->ldapImport->importFaecher('ou=Projects,ou=default-school,ou=SCHOOLS,dc=linuxmuster,dc=lan');
        return $this->redirect($request->headers->get('referer'));
    }
    
}
