<?php

namespace App\Controller\Admin;

use App\Entity\Bien;
use App\Entity\Contact;
use App\Entity\Paramettre;
use App\Entity\Proposition;
use App\Entity\Type;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(TypeCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('GROUPIMMO');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addJsFile('js/admin.js');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Bien', 'fas fa-home', Bien::class);
        yield MenuItem::linkToCrud('Type', 'fas fa-list', Type::class);
        yield MenuItem::linkToCrud('Propositions', 'fas fa-envelope', Proposition::class);
        yield MenuItem::linkToCrud('Contacts', 'fas fa-address-book', Contact::class);
        yield MenuItem::linkToCrud('Paramètres', 'fas fa-cog', Paramettre::class)
            ->setAction('edit')
            ->setEntityId(1); // ID de votre unique entrée
    }
}
