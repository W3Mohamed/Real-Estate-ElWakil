<?php
namespace App\Service;
use App\Entity\Promoteur;
use App\Entity\Bien;
use Doctrine\ORM\EntityManagerInterface;

class TerrainMatching
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function findPotentialTerrainsForPromoteur(Promoteur $promoteur, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->em->getRepository(Bien::class)->createQueryBuilder('b');
        
        // Exemple de critères de matching (à adapter)
        $qb->where('b.superficie >= :superficieMin')
           ->setParameter('superficieMin', $promoteur->getSuperficieMin());

        $qb->andWhere('b.superficie <= :superficieMax')
           ->setParameter('superficieMax', $promoteur->getSuperficieMax());
        
        if ($promoteur->getWilayas()) {
            $qb->andWhere('b.wilaya IN (:wilayas)')
               ->setParameter('wilayas', $promoteur->getWilayas());
        }

        if ($promoteur->getCommune()) {
            $qb->andWhere('b.commune = :commune')
               ->setParameter('commune', $promoteur->getCommune());
        }
        
        if ($promoteur->getType()) {
            $qb->andWhere('b.type IN (:type)')
               ->setParameter('type', $promoteur->getType());
        }

        $qb->distinct();

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }
        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }
}