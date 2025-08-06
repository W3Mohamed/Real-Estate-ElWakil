<?php

namespace App\Controller\Admin;

use App\Entity\Clients;
use App\Entity\Type;
use App\Entity\Wilaya;
use App\Repository\CommuneRepository;
use App\Service\BienMatchingService;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;

class ClientsCrudController extends AbstractCrudController
{
    public function __construct(private BienMatchingService $matchingService) {}

    public function createEntity(string $entityFqcn)
    {
        $entity = parent::createEntity($entityFqcn);
        $entity->setCreatedAt(new \DateTimeImmutable());
        
        return $entity;
    }

    public static function getEntityFqcn(): string
    {
        return Clients::class;
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom', 'Nom du client'),
            TextField::new('telephone', 'Numéro de téléphone'),

            // Pour les wilayas (sélection multiple)
            AssociationField::new('wilayas')
                ->setFormTypeOption('choice_label', 'nom')
                ->setFormTypeOption('multiple', true) // Permettre plusieurs sélections
                ->setFormTypeOption('by_reference', false)
                ->formatValue(function ($value, $entity) {
                    return implode(', ', $entity->getWilayas()->map(fn(Wilaya $w) => $w->getNom())->toArray());
                }),

            // Pour la commune (si ManyToOne est conservé)
            AssociationField::new('commune')
                ->setFormTypeOptions([
                    'choice_label' => 'nom',
                    'placeholder' => 'Choisissez une commune',
                ])
                ->formatValue(function ($value, $entity) {
                    return $entity->getCommune()?->getNom();
                }),
                
            AssociationField::new('type')
                ->setFormTypeOption('choice_label', 'libelle')
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('by_reference', false)
                ->formatValue(function ($value, $entity) {
                    return implode(', ', $entity->getType()->map(fn(Type $w) => $w->getLibelle())->toArray());
                }),
            
            ChoiceField::new(('transaction'))
                ->setChoices([
                    'Achat' => 'vente',
                    'Location' => 'location'
                ])
                ->setRequired(true)
                ->renderExpanded(false), // Affiche sous forme de liste déroulante
                
            IntegerField::new('budjetMin', 'Budget Min'),
            IntegerField::new('budjetMax', 'Budget Max'),

            ChoiceField::new('paiement')
                ->setChoices([
                    'Par banque' => 'banque',
                    'Normal' => 'normal'
                ]),

            IntegerField::new('potentialBiensCount', 'Biens Potentiels')
                ->setTemplatePath('admin/field/client_potential_biens.html.twig')
                ->onlyOnIndex()
                ->setSortable(false)
                ->formatValue(function ($value, Clients $entity) {
                    return $this->countPotentialBiens($entity);
                }),

            DateTimeField::new('createdAt', 'Date de création')
                ->setFormat('dd/MM/Y HH:mm')
                ->onlyOnIndex() // Ne s'affiche que dans la liste
                ->hideWhenCreating() // Cache le champ dans le formulaire de création
                ->setFormTypeOption('disabled', true) // Empêche la modification si affiché
                        
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

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            // Filtre pour les wilayas
            ->add(EntityFilter::new('wilayas', 'Wilaya(s)')
                ->setFormTypeOption('value_type_options', [
                    'class' => 'App\Entity\Wilaya',
                    'multiple' => true,
                    'choice_label' => 'nom',
                    'placeholder' => 'Sélectionner wilayas...',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('w')
                            ->orderBy('w.nom', 'ASC');
                    }
                ])
                ->canSelectMultiple()
            )
            
            // Filtre pour le type
            ->add(EntityFilter::new('type', 'Type(s)')
                ->setFormTypeOption('value_type_options', [
                    'class' => 'App\Entity\Type',
                    'multiple' => true,
                    'choice_label' => 'libelle',
                    'placeholder' => 'Sélectionner types...',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('t')
                            ->orderBy('t.libelle', 'ASC');
                    }
                ])
                ->canSelectMultiple()
            )
            
            // Filtres pour budget
            ->add(NumericFilter::new('budjetMin', 'Budget minimum')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Montant minimum...',
                        'min' => 0,
                        'step' => 1000
                    ]
                ])
            )
            ->add(NumericFilter::new('budjetMax', 'Budget maximum')
                ->setFormTypeOptions([
                    'attr' => [
                        'placeholder' => 'Montant maximum...',
                        'min' => 0,
                        'step' => 1000
                    ]
                ])
                    );
            

    }
    
}
