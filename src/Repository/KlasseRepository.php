<?php

namespace App\Repository;

use App\Entity\Klasse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Klasse>
 */
class KlasseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Klasse::class);
    }

       public function findOneByDn($dn): ?Klasse
       {
           return $this->createQueryBuilder('k')
               ->andWhere('k.dn = :val')
               ->setParameter('val', $dn)
               ->getQuery()
               ->getOneOrNullResult()
           ;
       }
    
          /**
           * @return Klasse[] Returns an array of Klasse objects
           */
          public function findVisible(): array
          {
              return $this->createQueryBuilder('k')
                      ->andWhere('k.visible = :val')
                      ->setParameter('val', true)
                      ->orderBy('k.label', 'ASC')
                      ->getQuery()
                      ->getResult()
                  ;
              }
       
    //    /**
    //     * @return Klasse[] Returns an array of Klasse objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('k')
    //            ->andWhere('k.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('k.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Klasse
    //    {
    //        return $this->createQueryBuilder('k')
    //            ->andWhere('k.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
