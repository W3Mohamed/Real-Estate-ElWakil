<?php
namespace App\Controller\Admin;

use App\Entity\Commune;
use App\Repository\CommuneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WilayaCommuneController extends AbstractController
{
    #[Route('/admin/api/communes', name: 'admin_api_communes')]
    public function getCommunesByWilaya(Request $request, CommuneRepository $communeRepo): JsonResponse
    {
        $wilayaId = $request->query->get('wilayaId');
        if (!$wilayaId) {
            return $this->json([], 400);
        }
    
        $communes = $communeRepo->findBy(['wilaya' => $wilayaId], ['nom' => 'ASC']);
    
        return $this->json(array_map(function($commune) {
            return [
                'id' => $commune->getId(),
                'text' => sprintf('%s (%s)', $commune->getNom(), $commune->getCodePostal())
            ];
        }, $communes));
    }
}