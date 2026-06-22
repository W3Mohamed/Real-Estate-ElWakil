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

    public function findPotentialBiensForClient(Clients $client, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->em->getRepository(Bien::class)->createQueryBuilder('b');
        
        // Exemple de critères de matching (à adapter)
        $qb->where('b.prix >= :budgetMin')
           ->setParameter('budgetMin', $client->getBudjetMin());

        $qb->andWhere('b.prix <= :budgetMax')
           ->setParameter('budgetMax', $client->getBudjetMax());
        
        if ($client->getWilayas()) {
            $qb->andWhere('b.wilaya IN (:wilayas)')
               ->setParameter('wilayas', $client->getWilayas());
        }

        if ($client->getCommune()) {
            $qb->andWhere('b.commune = :commune')
               ->setParameter('commune', $client->getCommune());
        }
        
        if ($client->getType()) {
            $qb->andWhere('b.type IN (:type)')
               ->setParameter('type', $client->getType());
        }

        if($client->getTransaction()) {
            $qb->andWhere('b.transaction = :transaction')
               ->setParameter('transaction', $client->getTransaction());
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

    public function findPotentialClientsForBien(Bien $bien, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->em->getRepository(Clients::class)->createQueryBuilder('c');
        
        // Exemple de critères de matching (à adapter)
        $qb->where('c.budjetMin <= :prix')
           ->setParameter('prix', $bien->getPrix());

        $qb->andWhere('c.budjetMax >= :prix')
           ->setParameter('prix', $bien->getPrix());
        
         if ($bien->getWilaya()) {
            $qb->leftJoin('c.wilayas', 'client_wilaya')
            ->andWhere('client_wilaya.id = :wilayaId OR SIZE(c.wilayas) = 0')
            ->setParameter('wilayaId', $bien->getWilaya()->getId());
        }  

        if ($bien->getCommune()) {
            $qb->andWhere('c.commune = :commune OR c.commune IS NULL')
            ->setParameter('commune', $bien->getCommune());
        }
        
        if ($bien->getType()) {
            $qb->leftJoin('c.type', 't') // 'types' doit être le nom de la propriété dans Clients.php
            ->andWhere('t.id = :typeId OR c.type IS EMPTY')
            ->setParameter('typeId', $bien->getType()->getId());
        }

        if($bien->getTransaction()) {
            $qb->andWhere('c.transaction = :transaction')
               ->setParameter('transaction', $bien->getTransaction());
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