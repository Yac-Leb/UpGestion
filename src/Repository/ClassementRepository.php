<?php

namespace App\Repository;

use App\Entity\Classement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Classement>
 */
class ClassementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Classement::class);
    }

    // Exemple de méthode personnalisée pour récupérer des classements par date
    public function findByDate(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.date = :date')
            ->setParameter('date', $date)
            ->orderBy('c.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Exemple de méthode pour récupérer le dernier classement
    public function findLastClassement(): ?Classement
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
