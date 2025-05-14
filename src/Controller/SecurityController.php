<?php
// src/Controller/SecurityController.php
namespace App\Controller;

use App\Repository\ParamettreRepository;
use App\Repository\TypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils,
    ParamettreRepository $paramettreRepository,
    TypeRepository $typeRepository): Response
    {
        $types = $typeRepository->findAll();
        $parametres = $paramettreRepository->find(1); 
        $error = $authenticationUtils->getLastAuthenticationError();
        
        return $this->render('security/login.html.twig', [
            'error' => $error,
            'parametres' => $parametres,
            'types' => $types
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout() {}

    // src/Controller/SecurityController.php
#[Route('/test-login', name: 'test_login')]
public function testLogin(UserPasswordHasherInterface $hasher, ParamettreRepository $repo)
{
    $user = $repo->find(1);
    if (!$user) {
        throw $this->createNotFoundException('Aucun utilisateur trouvé');
    }
    $isValid = $hasher->isPasswordValid($user, 'azerty');
    
    dd([
        'user_exists' => (bool)$user,
        'password_match' => $isValid,
        'user_class' => get_class($user),
        'stored_hash' => $user->getPwd()
    ]);
}
}