<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ClientsRepository;
use App\Service\BienMatchingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

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
    public function acheteur(ClientsRepository $clientsRepository,BienMatchingService $bienMatching): Response
    {
        $clients = $clientsRepository->findBy([], ['id' => 'DESC']);
        $nbClients = count($clients);
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
        
        return $this->render('agence/acheteur.html.twig', [
            'biens' => $biens,
            'nbBiens' => $nbBiens,
            'nbClients' => $nbClients,
            'clients' => $clients,
            'user' => $user,
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
