<?php

namespace App\Controller\Admin;

use App\Entity\Fach;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class FachCrudController extends AbstractCrudController
{
    public function __construct(
    ){}
    
    public static function getEntityFqcn(): string
    {
        return Fach::class;
    }

    public function configureActions(Actions $actions): Actions {
        $importFromLdap = Action::new('ldapImportFaecher')
        ->setLabel('Import')->createAsGlobalAction()->linkToUrl('/ldap/import/faecher/');
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
