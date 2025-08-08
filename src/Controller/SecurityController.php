<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\ParamettreRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils,TypeRepository $typeRepository,
        ParamettreRepository $paramettreRepository): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $types = $typeRepository->findAll();
        $parametres = $paramettreRepository->find(1);
        return $this->render('security/login.html.twig', [
            'types' => $types,
            'parametres' => $parametres,
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/sign', name: 'sign')]
    public function sign(Request $request,
     TypeRepository $typeRepository,
     ParamettreRepository $paramettreRepository,
     EntityManagerInterface $entityManager): Response
    {
        $types = $typeRepository->findAll();
        $parametres = $paramettreRepository->find(1);

        // Création du formulaire
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {            
            // Définition des valeurs par défaut
            $user->setRoles(['ROLE_AGENCE']);
            $user->setStatus(true);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setSubscribedAt(new \DateTimeImmutable());
            $user->setDuration(1); // Durée par défaut à 1 pour les nouveaux utilisateurs

            // Enregistrement
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirection + message de succès
            $this->addFlash('success', 'Votre compte a été créé avec succès !');
            return $this->redirectToRoute('app_login');
        }
            return $this->render('security/sign.html.twig', [
                'registrationForm' => $form->createView(),
                'types' => $types,
                'parametres' => $parametres,
            ]);
        
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
