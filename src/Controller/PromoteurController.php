<?php

namespace App\Controller;

use App\Repository\BienRepository;
use App\Repository\PromoteurRepository;
use App\Service\TerrainMatching;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PromoteurController extends AbstractController
{
    #[Route('/promoteur', name: 'promoteur')]
    public function index(TerrainMatching $terrainMatching, 
     PromoteurRepository $promoteurRepository,Request $request): Response
    {
        $user = $this->getUser();
        $promoteur = $promoteurRepository->find($user);

        $limit = 10;
        $page = $request->query->getInt('page', 1);
        $offset = ($page - 1) * $limit;

        $allTerrains = $terrainMatching->findPotentialTerrainsForPromoteur($promoteur);
        $totalTerrains = count($allTerrains);

        $terrainsPaginated = $terrainMatching->findPotentialTerrainsForPromoteur($promoteur, $limit, $offset);

        // Calcul des jours restants pour l'abonnement
        $now = new \DateTimeImmutable();
        $subscriptionEnd = $promoteur->getSubscribedAt()->add(new \DateInterval('P' . $promoteur->getDuration() . 'M'));
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

        return $this->render('promoteur/index.html.twig', [
            'terrains' => $terrainsPaginated,
            'totalTerrains' => $totalTerrains,
            'currentPage' => $page,
            'maxPages' => ceil($totalTerrains / $limit),
            'days_remaining' => $daysRemaining,
            'status_class' => $statusClass,
            'status_text' => $statusText,
            'promoteur' => $promoteur
        ]);
    }
}
