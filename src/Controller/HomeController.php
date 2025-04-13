<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'accueil')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('contact.html.twig');
    }

    #[Route('/biens', name: 'biens')]
    public function biens(): Response
    {
        return $this->render('biens.html.twig');
    }

    #[Route('/detail', name: 'detail')]
    public function detail(): Response
    {
        return $this->render('detail.html.twig');
    }

    #[Route('/apropos', name: 'about')]
    public function about(): Response
    {
        return $this->render('apropos.html.twig');
    }
}
