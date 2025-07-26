<?php
namespace App\Service;

use App\Entity\Clients;
use App\Entity\Bien;
use Doctrine\ORM\EntityManagerInterface;

class BienMatchingService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function findPotentialBiensForClient(Clients $client): array
    {
        $qb = $this->em->getRepository(Bien::class)->createQueryBuilder('b');
        
        // Exemple de critères de matching (à adapter)
        $qb->where('b.prix <= :budget')
           ->setParameter('budget', $client->getBudjet());
        
        if ($client->getWilaya()) {
            $qb->andWhere('b.wilaya = :wilaya')
               ->setParameter('wilaya', $client->getWilaya());
        }

        if ($client->getCommune()) {
            $qb->andWhere('b.commune = :commune')
               ->setParameter('commune', $client->getCommune());
        }
        
        if ($client->getType()) {
            $qb->andWhere('b.type = :type')
               ->setParameter('type', $client->getType());
        }

        return $qb->getQuery()->getResult();
    }
}