<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Dom\Text;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(
        EntityManagerInterface $entityManager,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $generateUsers = Action::new('generateUsers', 'Générer des comptes', 'fas fa-plus-circle')
            ->linkToUrl(function () {
                return $this->adminUrlGenerator->setRoute('admin_generate_users')->generateUrl();
            })
            ->addCssClass('btn btn-success')
            ->createAsGlobalAction(); // Action globale sur la page

        return $actions
            ->add(Crud::PAGE_INDEX, $generateUsers)
            ->setPermission('generateUsers', 'ROLE_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('username', 'Nom d\'utilisateur'),
            TextField::new('password', 'Mot de passe'),
            BooleanField::new('status', 'Statut')
                ->renderAsSwitch(false),
            DateTimeField::new('createdAt', 'Date de création')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->onlyOnIndex(),
            DateTimeField::new('subscribedAt', 'Date d\'abonnement')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->onlyOnIndex(),
            IntegerField::new('duration', 'Durée d\'abonnement (en mois)')
                ->setHelp('Durée de l\'abonnement en mois.'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setSearchFields(['username'])
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    #[Route('/admin/generate-users', name: 'admin_generate_users')]
    public function generateUsers(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $count = (int) $request->request->get('count', 5);
            $count = max(1, min(50, $count)); // Limite entre 1 et 50

            $createdUsers = [];

            for ($i = 1; $i <= $count; $i++) {
                // Générer un nom d'utilisateur unique
                $username = $this->generateUniqueUsername();
                
                // Générer un mot de passe aléatoirement
                $plainPassword = $this->generateRandomPassword();

                $user = new User();
                $user->setUsername($username);
                $user->setRoles(['ROLE_AGENCE']);
                $user->setStatus(true);
                $user->setCreatedAt(new \DateTimeImmutable());
                $user->setSubscribedAt(new \DateTimeImmutable());
                $user->setDuration(1);
                $user->setPassword($plainPassword);

                $this->entityManager->persist($user);

                $createdUsers[] = [
                    'username' => $username,
                    'password' => $plainPassword
                ];
            }

            $this->entityManager->flush();

            // Stocker les informations en session pour les afficher
            $request->getSession()->set('created_users', $createdUsers);

            $this->addFlash('success', sprintf('%d comptes ont été créés avec succès!', $count));

            // Rediriger vers la page des utilisateurs
            $url = $this->adminUrlGenerator
                ->setController(UserCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            return new RedirectResponse($url);
        }

        // Afficher le formulaire
        return $this->render('admin/generate_users.html.twig');
    }

    #[Route('/admin/show-created-users', name: 'admin_show_created_users')]
    public function showCreatedUsers(Request $request): Response
    {
        $createdUsers = $request->getSession()->get('created_users', []);
        $request->getSession()->remove('created_users');

        return $this->render('admin/show_created_users.html.twig', [
            'created_users' => $createdUsers
        ]);
    }

    private function generateUniqueUsername(): string
    {
        do {
            $username = 'agence_' . uniqid();
        } while ($this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]));

        return $username;
    }

    private function generateRandomPassword(int $length = 12): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }
}