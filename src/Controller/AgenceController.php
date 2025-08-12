<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\BienRepository;
use App\Repository\ClientsRepository;
use App\Repository\CommuneRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Repository\WilayaRepository;
use App\Service\BienMatchingService;
use Doctrine\ORM\Mapping\Id;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Constraints\All;

final class AgenceController extends AbstractController
{
    #[Route('/agence', name: 'agence')]
    public function index(BienRepository $bienRepository,
     ClientsRepository $clientsRepository, UserRepository $userRepository): Response
    {
        $nbBiens = count($bienRepository->findAll());
        $nbClients = count($clientsRepository->findAll());

        $user = $this->getUser();
        $utilisateur = $userRepository->find($user);

        $now = new \DateTimeImmutable();
        // Calcul des jours restants
        $subscriptionEnd = $utilisateur->getSubscribedAt()->add(new \DateInterval('P' . $utilisateur->getDuration() . 'M'));
        $daysRemaining = $now->diff($subscriptionEnd)->days;

        // Déterminer la classe CSS en fonction des jours restants
        $statusClass = 'bg-green-100 text-green-800'; // Par défaut
        $statusText = 'Abonnement Actif';
        
        if ($daysRemaining <= 5) {
            $statusClass = 'bg-red-100 text-red-800';
            $statusText = 'Expire bientôt';
        } elseif ($daysRemaining <= 0) {
            $statusClass = 'bg-gray-100 text-gray-800';
            $statusText = 'Abonnement expiré';
        }
        
        return $this->render('agence/index.html.twig', [
            'user' => $user,
            'nbBiens' => $nbBiens,
            'nbClients' => $nbClients,
            'days_remaining' => $daysRemaining,
            'status_class' => $statusClass,
            'status_text' => $statusText,
            'subscription_end' => $subscriptionEnd,
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/agence/acheteur', name: 'acheteur')]
    public function acheteur(ClientsRepository $clientsRepository,
     BienMatchingService $bienMatching,Request $request,
     BienRepository $bienRepository,WilayaRepository $wilayaRepository,
     CommuneRepository $communeRepository): Response
    {
        $idBien = $request->query->get('id') ?? null;
        $bien = null;
        if ($idBien !== null) {
            $bien = $bienRepository->find($idBien);
        }

        $wilayaId = $request->query->get('wilaya');
        $commune = $request->query->get('commune');
        $search = $request->query->get('search');
        $prixMin = $request->query->get('prixMin');
        $prixMax = $request->query->get('prixMax');
        $transaction = $request->query->get('transaction');
        $paiement = $request->query->get('paiement');

        $wilayas = $wilayaRepository->findAll();
        $communes = [];
        if ($wilayaId) {
            $communes = $communeRepository->findBy(['wilaya' => $wilayaId],['nom' => 'ASC']);
        }

        //$sort = $request->query->all('sort') ?? [];
        $sort = $request->query->all('sort');
        $sortByNbBiens = null;
        $page = $request->query->getInt('page', 1); // Page courante (1 par défaut)
        $limit = 20; // Nombre d'éléments par page
        
        // Calcul de l'offset
        $offset = ($page - 1) * $limit;

        if($request->query->get('id') !== null) {
            $id = $request->query->get('id');
            $bien = $bienRepository->find($id);
            $clients = $bienMatching->findPotentialClientsForBien($bien, $limit, $offset);
            $totalClients = count($bienMatching->findPotentialClientsForBien($bien));
        }else{
            // Cas général avec filtres
            $queryBuilder = $clientsRepository->createQueryBuilder('c');
             //   ->orderBy('c.id', 'DESC');
            
            // Application des filtres
            if ($wilayaId) {
                $queryBuilder->innerJoin('c.wilayas', 'w')
                    ->andWhere('w.id = :wilayaId')
                    ->setParameter('wilayaId', (int)$wilayaId);
            }

            if ($commune) {
                $queryBuilder->andWhere('c.commune = :communeId')
                    ->setParameter('communeId', $commune);
            }

            if ($search) {
                $queryBuilder->andWhere('c.nom LIKE :search')
                    ->setParameter('search', '%'.$search.'%');
            }

            if ($prixMin) {
                $queryBuilder->andWhere('c.budjetMin >= :prixMin')
                    ->setParameter('prixMin', $prixMin);
            }

            if ($prixMax) {
                $queryBuilder->andWhere('c.budjetMax <= :prixMax')
                    ->setParameter('prixMax', $prixMax);
            }

            if ($transaction) {
                $queryBuilder->andWhere('c.transaction = :transaction')
                    ->setParameter('transaction', $transaction);
            }

            if ($paiement) {
                $queryBuilder->andWhere('c.paiement = :paiement')
                    ->setParameter('paiement', $paiement);
            }

            $orderApplied = false;
            foreach ($sort as $field => $direction) {
                if (in_array(strtoupper($direction), ['ASC', 'DESC'])) {
                    // Vérification des champs autorisés pour le tri
                    $allowedFields = ['nom', 'telephone', 'transaction', 'type', 'wilayas', 'commune', 'paiement', 'nbBiens', 'date_creation'];
                    if (in_array($field, $allowedFields)) {
                        // Cas particulier pour les relations
                        if ($field === 'wilayas') {
                            $queryBuilder->leftJoin('c.wilayas', 'w')
                                ->addOrderBy('w.nom', $direction);
                        } elseif ($field === 'commune') {
                            $queryBuilder->leftJoin('c.commune', 'co')
                                ->addOrderBy('co.nom', $direction);
                        }elseif ($field === 'type') {
                            $queryBuilder->leftJoin('c.type', 't')
                                ->addOrderBy('t.libelle', $direction);
                        }elseif ($field === 'date_creation') {
                            $queryBuilder->addOrderBy('c.createdAt', $direction);
                        }elseif ($field === 'nbBiens'){
                            // Pour nbBiens, on ne peut pas trier dans la requête SQL
                            // car c'est calculé après. On marque juste qu'un tri sera appliqué
                            $sortByNbBiens = ['field' => 'nbBiens', 'direction' => $direction];
                        }else{
                            $queryBuilder->addOrderBy('c.' . $field, $direction);
                        }
                        $orderApplied = true;
                    }
                }
            }
            // Ordre par défaut seulement si aucun tri personnalisé n'a été appliqué
            if (!$orderApplied) {
                $queryBuilder->orderBy('c.id', 'DESC');
            }

            // Comptage total - version sécurisée
            $countQuery = clone $queryBuilder;
            $countQuery->select('COUNT(c.id)');
            
            try {
                $totalClients = (int) $countQuery->getQuery()->getSingleScalarResult();
            } catch (\Doctrine\ORM\NoResultException $e) {
                $totalClients = 0;
            } 
            
            // Récupération des clients paginés
            $clients = $queryBuilder
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

        }

        foreach ($clients as $client) {
            // Format the price from cents to a readable format
            $client->formatedMin = $this->formatPrix($client->getBudjetMin());
            $client->formatedMax = $this->formatPrix($client->getBudjetMax());
        }

        $biens = [];
        $nbBiens = [];

        foreach ($clients as $client) {
            $biensClient = $bienMatching->findPotentialBiensForClient($client);
            $biens[$client->getId()] = $biensClient;
            $nbBiens[$client->getId()] = count($biensClient);
        }   
        $allClients = $clientsRepository->findAll();
        // Tri par nbBiens si demandé
        if (isset($sortByNbBiens)) {
            usort($allClients, function($a, $b) use ($nbBiens, $sortByNbBiens) {
                $countA = $nbBiens[$a->getId()] ?? 0;
                $countB = $nbBiens[$b->getId()] ?? 0;
                
                if ($sortByNbBiens['direction'] === 'ASC') {
                    return $countA <=> $countB;
                } else {
                    return $countB <=> $countA;
                }
            });
        }

        $user = $this->getUser();
        $totalPages = $totalClients > 0 ? ceil($totalClients / $limit) : 1; // Si aucun client, on affiche une seule page

        return $this->render('agence/acheteur.html.twig', [
            'biens' => $biens,
            'nbBiens' => $nbBiens,
            'nbClients' => $totalClients,
            'clients' => $clients,
            'user' => $user,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'limit' => $limit,
            'wilayas' => $wilayas,
            'communes' => $communes,
            'currentWilaya' => $wilayaId,
            'currentCommune' => $commune,
            'idBien' => $idBien,
            'bien' => $bien ?? null,
        ]);
    }

    #[Route('/agence/biens', name: 'liste')]
    public function liste(Request $request,BienRepository $bienRepository,
     BienMatchingService $bienMatching,ClientsRepository $clientsRepository,
     TypeRepository $typeRepository): Response
    {
        $search = $request->query->get('search');
        $typeId = $request->query->get('type');
        $transaction = $request->query->get('transaction');

        $user = $this->getUser();
        $types = $typeRepository->findAll();
        $page = $request->query->getInt('page', 1); // Page courante (1 par défaut)
        $limit = 20; // Nombre d'éléments par page
        
        // Calcul de l'offset
        $offset = ($page - 1) * $limit;
        
        if($request->query->get('id') !== null){
            $id = $request->query->get('id');
            $client = $clientsRepository->find($id);
            $biens = $bienMatching->findPotentialBiensForClient($client, $limit, $offset);
            $totalBiens = count($bienMatching->findPotentialBiensForClient($client));
        }else{
            $queryBuilder = $bienRepository->createQueryBuilder('b')
                ->orderBy('b.id', 'DESC');
            // Application des filtres
            if ($search) {
                $queryBuilder
                    ->leftJoin('b.wilaya', 'w') // 'b.wilaya' est la relation vers l'entité Wilaya
                    ->leftJoin('b.commune', 'c') // 'b.commune' est la relation vers l'entité Commune
                    ->andWhere('b.libelle LIKE :search 
                            OR w.nom LIKE :search 
                            OR c.nom LIKE :search')
                    ->setParameter('search', '%'.$search.'%');
            }
            if ($typeId) {
                $queryBuilder->andWhere('b.type = :typeId')
                    ->setParameter('typeId', (int)$typeId);
            }
            if ($transaction) {
                $queryBuilder->andWhere('b.transaction = :transaction')
                    ->setParameter('transaction', $transaction);
            }
             // Comptage total - version sécurisée
            $countQuery = clone $queryBuilder;
            $countQuery->select('COUNT(b.id)');
            
            try {
                $totalBiens = (int) $countQuery->getQuery()->getSingleScalarResult();
            } catch (\Doctrine\ORM\NoResultException $e) {
                $totalBiens = 0;
            }   
            $biens = $queryBuilder
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
        }
        $nbAcheteurs = count($clientsRepository->findAll());

        foreach ($biens as $bien) {
            // Format the price from cents to a readable format
            $bien->formatPrix = $this->formatPrix($bien->getPrix());
        }

        $clients = [];
        $nbClients = [];
        foreach ($biens as $bien) {
            $clientBien = $bienMatching->findPotentialClientsForBien($bien);
            $clients[$bien->getId()] = $clientBien;
            $nbClients[$bien->getId()] = count($clientBien);
        }  
        $totalPages = $totalBiens > 0 ? ceil($totalBiens / $limit) : 1; // Si aucun bien, on affiche une seule page

        return $this->render('agence/liste.html.twig', [
            'user' => $user,
            'biens' => $biens,
            'nbBiens' => $totalBiens,
            'clients' => $clients,
            'nbClients' => $nbClients,
            'nbAcheteurs' => $nbAcheteurs,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'limit' => $limit,
            'types' => $types,
        ]);
    }

    private function formatPrix(?int $prixCentimes): string
    {
        if ($prixCentimes === null) {
            return 'Prix non disponible';
        }
    
        // Convertir les centimes en unités standard (1 million = 10000 centimes)
        $prixStandard = $prixCentimes * 100; // 10000 centimes = 1 million DZD
        
        $milliards = floor($prixStandard / 1000000000);
        $reste = $prixStandard % 1000000000;
        $millions = floor($reste / 1000000);
        $milliers = floor(($reste % 1000000) / 1000);
        $unites = $reste % 1000;
    
        $result = '';
        
        if ($milliards > 0) {
            $result .= number_format($milliards, 0, ',', ' ') . ' Md';
        }
        
        if ($millions > 0) {
            if (!empty($result)) {
                $result .= ' ';
            }
            $result .= number_format($millions, 0, ',', ' ') . ' M';
        }
        
        if ($milliers > 0) {
            if (!empty($result)) {
                $result .= ' ';
            }
            $result .= number_format($milliers, 0, ',', ' ') . ' Mille';
        }
        
        if ($unites > 0 && empty($result)) {
            $result .= number_format($unites, 0, ',', ' ');
        }
    
        if (empty($result)) {
            return '0';
        }
    
        return $result;
    }
}
