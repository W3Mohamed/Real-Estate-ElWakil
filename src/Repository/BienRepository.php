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

    public function findWithImages(int $id): ?Bien
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.images', 'i')
            ->addSelect('i') // Charge les images en même temps que le bien
            ->where('b.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBiensALouer()
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.images', 'i')
            ->addSelect('i') // Charge la première image
            ->andWhere('b.transaction = :transaction')
            ->setParameter('transaction', 'location')
            ->orderBy('b.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBiensAVendre()
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.images', 'i')
            ->addSelect('i') // Charge la première image
            ->andWhere('b.transaction = :transaction')
            ->setParameter('transaction', 'vente')
            ->orderBy('b.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findSimilarBiens(Bien $bien, int $limit = 3): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.images', 'i')
            ->addSelect('i') // Charge les images
            ->where('b.transaction = :transaction')
            ->andWhere('b.type = :type')
            ->andWhere('b.id != :currentId')
            ->setParameter('transaction', $bien->getTransaction())
            ->setParameter('type', $bien->getType())
            ->setParameter('currentId', $bien->getId())
            ->orderBy('b.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
