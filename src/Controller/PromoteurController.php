<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PromoteurController extends AbstractController
{
    #[Route('/promoteur', name: 'promoteur')]
    public function index(): Response
    {
        return $this->render('promoteur/index.html.twig', [
            'controller_name' => 'PromoteurController',
        ]);
    }
}
