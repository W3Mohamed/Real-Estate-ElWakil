<?php

namespace App\Repository;

use App\Entity\Bien;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bien>
 */
class BienRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bien::class);
    }

    public function findBiensALouer()
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.transaction = :transaction')
            ->setParameter('transaction', 'location')
            ->orderBy('b.id', 'DESC') // ou autre critère de tri
            ->getQuery()
            ->getResult();
    }

    public function findBiensAVendre()
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.transaction = :transaction')
            ->setParameter('transaction', 'vente')
            ->orderBy('b.id', 'DESC') // ou autre critère de tri
            ->getQuery()
            ->getResult();
    }

    public function findSimilarBiens(Bien $currentBien, int $maxResults = 6)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.transaction = :transaction')
            ->andWhere('b.type = :type')
            ->andWhere('b.id != :currentId')
            ->setParameter('transaction', $currentBien->getTransaction())
            ->setParameter('type', $currentBien->getType())
            ->setParameter('currentId', $currentBien->getId())
            ->orderBy('b.id', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }
}
