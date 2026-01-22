<?php

namespace App\Controller\Admin;

use App\Entity\Promoteur;
use App\Entity\Type;
use App\Service\TerrainMatching;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Validator\Constraints\Date;

class PromoteurCrudController extends AbstractCrudController
{
    public function __construct(private TerrainMatching $matchingService) {}

    public function createEntity(string $entityFqcn)
    {
        $entity = parent::createEntity($entityFqcn);
        $entity->setCreatedAt(new \DateTimeImmutable());
        
        return $entity;
    }

    public static function getEntityFqcn(): string
    {
        return Promoteur::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom'),
            TextField::new('telephone'),
            // Association avec l'entité Type
            AssociationField::new('type')
                ->setFormTypeOptions([
                    'choice_label' => 'libelle',
                    'placeholder' => 'Choisissez un type',
                ])
                ->formatValue(function ($value, $entity) {
                    return $entity->getType()?->getLibelle();
                }),
            IntegerField::new('superficie_min'),
            IntegerField::new('superficie_max'),
            // Association avec l'entité Wilaya (ManyToMany)
            AssociationField::new('wilayas')
                ->setFormTypeOption('choice_label', 'nom')
                ->setFormTypeOption('multiple', true) // Permettre plusieurs sélections
                ->setFormTypeOption('by_reference', false)
                ->formatValue(function ($value, $entity) {
                    return implode(', ', $entity->getWilayas()->map(fn($w) => $w->getNom())->toArray());
                }),
            // Association avec l'entité Commune (ManyToOne)
            AssociationField::new('commune')
                ->setFormTypeOptions([
                    'choice_label' => 'nom',
                    'placeholder' => 'Choisissez une commune',
                ])
                ->formatValue(function ($value, $entity) {
                    return $entity->getCommune()?->getNom();
                }),
                
            IntegerField::new('countPotontielTerrains', 'Terrains potentiels')
                ->setTemplatePath('admin/field/terrain_potential_promoteur.html.twig')
                ->onlyOnIndex()
                ->setSortable(false)
                ->formatValue(function ($value,Promoteur $entity) {
                    return $this->countPotontielTerrains($entity);
                }),
            DateTimeField::new('createdAt')
                ->onlyOnIndex()
                ->setFormat('dd/MM/yyyy HH:mm')
                ->hideWhenCreating()
                ->setFormTypeOption('disabled', true),
        ];
    }

    public function configreActions(Actions $actions): Actions
    {
        $viewPotentialTerrains = Action::new('viewPotentialTerrains', 'Voir les terrains potentiels')
            ->linkToCrudAction('viewPotentialTerrains')
            ->setCssClass('btn btn-info');

        return $actions
            ->add(Crud::PAGE_INDEX, $viewPotentialTerrains);
    }

    public function countPotontielTerrains(Promoteur $promoteur): int
    {  
        return count($this->matchingService->findPotentialTerrainsForPromoteur($promoteur));
    }

    public function viewPotentialTerrains()
    {
        $promoteur = $this->getContext()->getEntity()->getInstance();
        $terrains = $this->matchingService->findPotentialTerrainsForPromoteur($promoteur);

        return $this->render('admin/potential_terrains.html.twig', [
            'promoteur' => $promoteur,
            'terrains' => $terrains,
        ]);
    }
    
}
