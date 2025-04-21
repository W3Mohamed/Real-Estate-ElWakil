<?php

namespace App\Controller;

use App\Entity\Proposition;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'accueil')]
    public function index(TypeRepository $typeRepository): Response
    {
        $types = $typeRepository->findAll();
        return $this->render('index.html.twig',[
            'types' => $types
        ]);
    }

    #[Route('/vendre', name: 'vendre')]
    public function vendre(Request $request, EntityManagerInterface $entityManager, TypeRepository $typeRepository): Response
    {
        $types = $typeRepository->findAll();
        if ($request->isMethod('POST')) {
            // Récupération des données du formulaire
            $data = $request->request->all();
            
            // Création d'une nouvelle proposition
            $proposition = new Proposition();
            $proposition->setNom($data['name']);
            $proposition->setTelephone($data['phone']);
            $proposition->setEmail($data['email']);
            
            // Récupération du type depuis la base de données
            $type = $typeRepository->find($data['type']);
            $proposition->setType($type);
            
            $proposition->setTransaction($data['transaction']);
            $proposition->setAdresse($data['adresse']);
            $proposition->setDescription($data['message']);
            
            // Enregistrement en base de données
            $entityManager->persist($proposition);
            $entityManager->flush();
            
            // Redirection vers l'accueil avec un message de succès
            $this->addFlash('success', 'Votre proposition a bien été enregistrée !');
            return $this->redirectToRoute('accueil', ['_fragment' => 'vendre']);
        }
        
        // Si méthode GET, affiche le formulaire avec les types
        return $this->redirectToRoute('accueil', ['_fragment' => 'vendre']);
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
