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

    public function findSimilarVenteBiens(Bien $bien, int $limit = 3): array
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.images', 'i')
            ->addSelect('i')
            ->where('b.transaction = :transaction')
            ->andWhere('b.type = :type')
            ->andWhere('b.wilaya = :wilaya')
            ->andWhere('b.id != :currentId')
            ->andWhere('b.prix BETWEEN :minPrix AND :maxPrix')
            ->setParameter('transaction', $bien->getTransaction())
            ->setParameter('type', $bien->getType())
            ->setParameter('wilaya', $bien->getWilaya())
            ->setParameter('currentId', $bien->getId())
            ->setParameter('minPrix', $bien->getPrix() - 2000000)
            ->setParameter('maxPrix', $bien->getPrix() + 2000000)
            ->orderBy('b.id', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function findSimilarLocationBiens(Bien $bien, int $limit = 3): array
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.images', 'i')
            ->addSelect('i')
            ->where('b.transaction = :transaction')
            ->andWhere('b.type = :type')
            ->andWhere('b.wilaya = :wilaya')
            ->andWhere('b.id != :currentId')
            ->setParameter('transaction', $bien->getTransaction())
            ->setParameter('type', $bien->getType())
            ->setParameter('wilaya', $bien->getWilaya())
            ->setParameter('currentId', $bien->getId())
            ->orderBy('b.id', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function findLastEight()
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.id', 'DESC')  // Tri par ID décroissant
            ->setMaxResults(8)         // Limite à 8 résultats
            ->getQuery()
            ->getResult();
    }
    
}
