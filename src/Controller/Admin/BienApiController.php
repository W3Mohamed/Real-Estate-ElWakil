<?php

namespace App\Controller\Admin;

use App\Entity\Bien;
use App\Repository\BienRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class BienApiController extends AbstractController
{
    #[Route('/admin/api/bien/{id}', name: 'admin_api_bien_details')]
    public function getBienDetails(int $id, BienRepository $bienRepo): JsonResponse
    {
        $bien = $bienRepo->find($id);
        
        if (!$bien) {
            return $this->json(['error' => 'Bien non trouvé'], 404);
        }
        
        // Retourner les informations nécessaires
        return $this->json([
            'id' => $bien->getId(),
            'wilaya' => [
                'id' => $bien->getWilaya() ? $bien->getWilaya()->getId() : null,
                'nom' => $bien->getWilaya() ? $bien->getWilaya()->getNom() : null
            ],
            'commune' => [
                'id' => $bien->getCommune() ? $bien->getCommune()->getId() : null,
                'nom' => $bien->getCommune() ? $bien->getCommune()->getNom() : null
            ]
        ]);
    }
}