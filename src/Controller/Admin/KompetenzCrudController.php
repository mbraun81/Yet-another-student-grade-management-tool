<?php
namespace App\Controller\Admin;

use App\Entity\Kompetenz;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use App\Entity\Fach;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class KompetenzCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Kompetenz::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm()->hideOnIndex();
        yield TextField::new('name');
        yield AssociationField::new('fach')->setQueryBuilder(
            fn (QueryBuilder $queryBuilder): QueryBuilder => $queryBuilder->getEntityManager()->getRepository(Fach::class)->findVisibleQueryBuilder()
        );
    }
}
