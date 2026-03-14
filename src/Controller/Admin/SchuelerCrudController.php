<?php

namespace App\Controller\Admin;

use App\Entity\Schueler;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class SchuelerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Schueler::class;
    }

    public function configureActions(Actions $actions): Actions {
        $importFromLdap = Action::new('ldapImportFaecher')
        ->setLabel('Import')->createAsGlobalAction()->linkToUrl('/ldap/import/schueler/');
        return $actions->add(Crud::PAGE_INDEX ,$importFromLdap);
    }
    
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm()->hideOnIndex();
        yield BooleanField::new('visible');
        yield TextField::new('label');
        yield TextField::new('dn');
    }
}
