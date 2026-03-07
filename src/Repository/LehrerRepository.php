<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Lehrer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lehrer>
 */
class LehrerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lehrer::class);
    }

    public function findByLdapUsername(string $username): ?Lehrer
    {
        return $this->findOneBy(['ldapUsername' => $username]);
    }
}
