<?php

namespace App\Controller\Admin;

use App\Entity\Kompetenzraster;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class KompetenzrasterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Kompetenzraster::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
