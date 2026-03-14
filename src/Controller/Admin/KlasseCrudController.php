<?php

namespace App\Controller\Admin;

use App\Entity\Klasse;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use App\Service\LdapImportService;
use App\Entity\Schueler;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class KlasseCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly LdapImportService $ldapImport,
        private readonly EntityManagerInterface  $em,
    ){}
    public static function getEntityFqcn(): string
    {
        return Klasse::class;
    }

    public function configureActions(Actions $actions): Actions {
        $importFromLdap = Action::new('ldapImportFaecher')
            ->setLabel('Import')
            ->createAsGlobalAction()
            ->linkToUrl('/ldap/import/klasse/');
        $importSchueler = Action::new('schueler')
            ->setLabel('Schüler importieren')
            ->linkToCrudAction('importSchueler')
            ;
        $importLehrer = Action::new('lehrer')
            ->setLabel('Lehrer importieren')
            ->linkToCrudAction('importLehrer')
            ;
            
        return $actions
            ->add(Crud::PAGE_INDEX , $importFromLdap)
            ->add(Crud::PAGE_INDEX , $importSchueler)
            ->add(Crud::PAGE_INDEX , $importLehrer)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
        ;
    }
    
    #[AdminRoute(path: '/import/schueler', name: 'admin_klasse_import_schueler')]
    public function importSchueler(AdminContext $context)
    {
        /** @var Klasse $klasse */
        $klasse = $context->getEntity()->getInstance();
        $this->em->persist($klasse);
        
        $parts = ldap_explode_dn($klasse->getDn(), 0);
        unset($parts['count']); // count entfernen
        unset($parts[0]); // cn entfernen
        $parentDn = implode(',', $parts);
        foreach ($this->ldapImport->searchOrganizationalPerson($parentDn) AS $dn=>$cn) {
            /** @var Schueler $schuler */
            $schueler = $this->ldapImport->importSchueler($dn);
            $klasse->addSchueler($schueler);
        }
        $this->em->flush();
        return $this->redirectToRoute('admin_klasse_index');
    }
    
    
    #[AdminRoute(path: '/import/lehrer', name: 'admin_klasse_import_lehrer')]
    public function importLehrer(AdminContext $context)
    {
        /** @var Klasse $klasse */
        $klasse = $context->getEntity()->getInstance();
        $this->em->persist($klasse);
        
        foreach ($this->ldapImport->importLehrerFromKlass($klasse->getDn()) AS $fachlehrer) {
            $klasse->addFachlehrer($fachlehrer);
        }
        $this->em->flush();
        return $this->redirectToRoute('admin_klasse_index');
    }
    
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm()->hideOnIndex();
        yield BooleanField::new('visible');
        yield TextField::new('label');
        yield AssociationField::new('schuelers');
        yield AssociationField::new('klassenlehrer');
        yield AssociationField::new('fachlehrer');
        yield TextField::new('dn')->hideOnIndex();
    }
}
