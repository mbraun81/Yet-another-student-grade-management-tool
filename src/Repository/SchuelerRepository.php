<?php
namespace App\Repository;

use App\Entity\Schueler;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Klasse;

/**
 *
 * @extends ServiceEntityRepository<Schueler>
 */
class SchuelerRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schueler::class);
    }

    /**
     *
     * @return Schueler[] Returns an array of Schueler objects
     */
    public function findByKlasse(Klasse $klasse): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.klasse = :val')
            ->setParameter('val', $klasse)
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByDn($dn): ?Schueler
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.dn = :val')
            ->setParameter('val', $dn)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // /**
    // * @return Schueler[] Returns an array of Schueler objects
    // */
    // public function findByExampleField($value): array
    // {
    // return $this->createQueryBuilder('s')
    // ->andWhere('s.exampleField = :val')
    // ->setParameter('val', $value)
    // ->orderBy('s.id', 'ASC')
    // ->setMaxResults(10)
    // ->getQuery()
    // ->getResult()
    // ;
    // }

    // public function findOneBySomeField($value): ?Schueler
    // {
    // return $this->createQueryBuilder('s')
    // ->andWhere('s.exampleField = :val')
    // ->setParameter('val', $value)
    // ->getQuery()
    // ->getOneOrNullResult()
    // ;
    // }
}
