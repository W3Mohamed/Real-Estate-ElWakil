<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\BienRepository;
use App\Repository\ClientsRepository;
use App\Repository\TypeRepository;
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
    public function index(): Response
    {
        $user = $this->getUser();
        
        return $this->render('agence/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/agence/acheteur', name: 'acheteur')]
    public function acheteur(ClientsRepository $clientsRepository,
     BienMatchingService $bienMatching,Request $request,
     BienRepository $bienRepository,WilayaRepository $wilayaRepository): Response
    {
        $wilayas = $wilayaRepository->findAll();
        $page = $request->query->getInt('page', 1); // Page courante (1 par défaut)
        $limit = 20; // Nombre d'éléments par page
        
        // Calcul de l'offset
        $offset = ($page - 1) * $limit;

        if($request->query->get('id') !== null) {
            $id = $request->query->get('id');
            $bien = $bienRepository->find($id);
            $clients = $bienMatching->findPotentialClientsForBien($bien);
            $totalClients = count($clients);
        }else{
            // Récupération des clients paginés
            $clients = $clientsRepository->findBy(
                [], 
                ['id' => 'DESC'],
                $limit, 
                $offset
            );          
            // Récupération du total avant pagination
            $totalClients = $clientsRepository->count([]);
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

        $user = $this->getUser();
        $totalPages = ceil($totalClients / $limit);

        return $this->render('agence/acheteur.html.twig', [
            'biens' => $biens,
            'nbBiens' => $nbBiens,
            'nbClients' => $totalClients,
            'clients' => $clients,
            'user' => $user,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'limit' => $limit,
            'wilayas' => $wilayas
        ]);
    }

    #[Route('/agence/biens', name: 'liste')]
    public function liste(Request $request,BienRepository $bienRepository,
     BienMatchingService $bienMatching,ClientsRepository $clientsRepository,
     TypeRepository $typeRepository): Response
    {
        $user = $this->getUser();
        $types = $typeRepository->findAll();
        $page = $request->query->getInt('page', 1); // Page courante (1 par défaut)
        $limit = 20; // Nombre d'éléments par page

        // Récupération du total avant pagination
        $totalBiens = $bienRepository->count([]);
        
        // Calcul de l'offset
        $offset = ($page - 1) * $limit;
        
        if($request->query->get('id') !== null){
            $id = $request->query->get('id');
            $client = $clientsRepository->find($id);
            $biens = $bienMatching->findPotentialBiensForClient($client);
            $totalBiens = count($biens);
        }else{
            // Récupération des biens paginés
            $biens = $bienRepository->findBy(
                [], 
                ['id' => 'DESC'],
                $limit, 
                $offset
            );
            $totalBiens = $bienRepository->count([]);
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
        $totalPages = ceil($totalBiens / $limit);
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
