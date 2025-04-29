<?php

namespace App\Controller\Admin;

use App\Entity\Bien;
use App\Entity\Contact;
use App\Entity\Paramettre;
use App\Entity\Proposition;
use App\Entity\Reservation;
use App\Entity\Type;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Statistiques
        $stats = [
            'biens' => $this->em->getRepository(Bien::class)->count([]),
            'biens_vente' => $this->em->getRepository(Bien::class)->count(['transaction' => 'vente']),
            'biens_location' => $this->em->getRepository(Bien::class)->count(['transaction' => 'location']),
            'reservations' => $this->em->getRepository(Reservation::class)->count([]),
            'propositions' => $this->em->getRepository(Proposition::class)->count([]),
            'contacts' => $this->em->getRepository(Contact::class)->count([]),
            'latest_biens' => $this->em->getRepository(Bien::class)->findBy([], ['id' => 'DESC'], 5),
            'latest_contacts' => $this->em->getRepository(Contact::class)->findBy([], ['id' => 'DESC'], 5),
        ];

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'dashboard_title' => 'Tableau de bord GROUPIMMO',
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('GROUPIMMO Admin')
            ->setFaviconPath('favicon-admin.ico')
            ->setTranslationDomain('admin');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('css/admin.css')
            ->addJsFile('js/admin.js')
            ->addWebpackEncoreEntry('admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        
        // Section Immobilier
        yield MenuItem::section('Immobilier');
        yield MenuItem::linkToCrud('Types de biens', 'fas fa-tags', Type::class);
        yield MenuItem::linkToCrud('Biens immobiliers', 'fas fa-home', Bien::class);
        
        // Section Interactions
        yield MenuItem::section('Interactions');
        yield MenuItem::linkToCrud('Réservations', 'fas fa-calendar-check', Reservation::class);
        yield MenuItem::linkToCrud('Propositions', 'fas fa-handshake', Proposition::class);
        yield MenuItem::linkToCrud('Messages', 'fas fa-envelope', Contact::class);
        
        // Section Configuration
        yield MenuItem::section('Configuration');
        yield MenuItem::linkToCrud('Paramètres', 'fas fa-cog', Paramettre::class)
            ->setAction('edit')
            ->setEntityId(1);
            
        yield MenuItem::linkToUrl('Retour au site', 'fas fa-globe', '/');
    }
}