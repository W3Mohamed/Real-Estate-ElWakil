<?php

namespace App\Controller\Admin;

use App\Entity\Clients;
use App\Repository\CommuneRepository;
use App\Service\BienMatchingService;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ClientsCrudController extends AbstractCrudController
{
    public function __construct(private BienMatchingService $matchingService) {}

    public static function getEntityFqcn(): string
    {
        return Clients::class;
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom', 'Nom du client'),
            TextField::new('telephone', 'Numéro de téléphone'),

            AssociationField::new('wilaya')
                ->setFormTypeOption('choice_label', 'nom')
                ->setFormTypeOption('query_builder', function (EntityRepository $er) {
                    return $er->createQueryBuilder('w')
                        ->orderBy('w.id', 'ASC');
                })
                ->formatValue(function ($value, $entity) {
                    return $entity->getWilaya() ? $entity->getWilaya()->getNom() : '';
                }),

            AssociationField::new('commune')
                ->setFormTypeOptions([
                    'choice_label' => 'nom',
                    'placeholder' => 'Choisissez d\'abord une wilaya',
                    'query_builder' => function(CommuneRepository $repo) {
                        return $repo->createQueryBuilder('c')
                            ->orderBy('c.nom', 'ASC');
                    }
                ])
                // Supprimez les autres options qui pourraient interférer
                ->formatValue(function ($value, $entity) {
                    return $entity->getCommune() ? $entity->getCommune()->getNom() : '';
                }),
                
            AssociationField::new('type')
                ->setFormTypeOption('choice_label', 'libelle')
                ->setFormTypeOption('query_builder', function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.id', 'ASC');
                })
                ->formatValue(function ($value, $entity) {
                    return $entity->getType() ? $entity->getType()->getLibelle() : '';
                }),
            IntegerField::new('budjet', 'Budget'),

            IntegerField::new('potentialBiensCount', 'Biens Potentiels')
                ->setTemplatePath('admin/field/client_potential_biens.html.twig')
                ->onlyOnIndex()
                ->setSortable(false)
                ->formatValue(function ($value, Clients $entity) {
                    return $this->countPotentialBiens($entity);
                })
            
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        // Ajoute une action pour voir les biens potentiels
        $viewPotentialBiens = Action::new('viewPotentialBiens', 'Voir biens', 'fa fa-home')
            ->linkToCrudAction('viewPotentialBiens');

        return $actions
            ->add(Crud::PAGE_INDEX, $viewPotentialBiens);
    }

    public function countPotentialBiens(Clients $client): int
    {
        return count($this->matchingService->findPotentialBiensForClient($client));
    }

    public function viewPotentialBiens()
    {
        $client = $this->getContext()->getEntity()->getInstance();
        $biens = $this->matchingService->findPotentialBiensForClient($client);

         // Créer une page personnalisée pour afficher les biens
        return $this->render('admin/potential_biens.html.twig', [
            'client' => $client,
            'biens' => $biens,
        ]);
    }
    
}
