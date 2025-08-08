<?php

namespace App\Controller;

use App\Entity\User;
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
}
