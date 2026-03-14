<?php
namespace App\Repository;

use App\Entity\Fach;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 *
 * @extends ServiceEntityRepository<Fach>
 */
class FachRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fach::class);
    }

    public function findOneByDn($dn): ?Fach
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.dn = :val')
            ->setParameter('val', $dn)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Fach[] Returns an array of Klasse objects
     */
    public function findVisible(): array
    {
        return $this->findVisibleQueryBuilder()
        ->getQuery()
        ->getResult()
        ;
    }
    
    public function findVisibleQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('f')
        ->andWhere('f.visible = :val')
        ->setParameter('val', true)
        ->orderBy('f.label', 'ASC');
    }
    
    // /**
    // * @return Fach[] Returns an array of Fach objects
    // */
    // public function findByExampleField($value): array
    // {
    // return $this->createQueryBuilder('f')
    // ->andWhere('f.exampleField = :val')
    // ->setParameter('val', $value)
    // ->orderBy('f.id', 'ASC')
    // ->setMaxResults(10)
    // ->getQuery()
    // ->getResult()
    // ;
    // }

    // public function findOneBySomeField($value): ?Fach
    // {
    // return $this->createQueryBuilder('f')
    // ->andWhere('f.exampleField = :val')
    // ->setParameter('val', $value)
    // ->getQuery()
    // ->getOneOrNullResult()
    // ;
    // }
}
