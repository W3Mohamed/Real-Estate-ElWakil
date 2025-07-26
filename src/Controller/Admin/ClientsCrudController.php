<?php

namespace App\Controller\Admin;

use App\Entity\Clients;
use App\Repository\CommuneRepository;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ClientsCrudController extends AbstractCrudController
{
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
            IntegerField::new('budjet', 'Budget')
            
        ];
    }
    
}
