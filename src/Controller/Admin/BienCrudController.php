<?php

namespace App\Controller\Admin;

use App\Entity\Bien;
use App\Entity\Images;
use App\Repository\CommuneRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Form\ImageFormType;


class BienCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Bien::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('libelle', 'Libellé');
        yield IntegerField::new('prix', 'Prix'); 
        yield ChoiceField::new('transaction')
            ->setChoices([
                'Vente' => 'vente',
                'Location' => 'location',
            ]);
        yield AssociationField::new('type', 'Type de bien')
            ->setFormTypeOption('choice_label', 'libelle')
            ->setFormTypeOption('query_builder', function (EntityRepository $er): QueryBuilder {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.libelle', 'ASC');
            })
            ->formatValue(function ($value, $entity) {
                return $entity->getType() ? $entity->getType()->getLibelle() : '';
            });
    
        // Wilaya et Commune doivent venir avant CollectionField
        yield AssociationField::new('wilaya')
            ->setFormTypeOption('choice_label', 'nom')
            ->setFormTypeOption('query_builder', function (EntityRepository $er) {
                return $er->createQueryBuilder('w')
                    ->orderBy('w.id', 'ASC');
            })
            ->formatValue(function ($value, $entity) {
                return $entity->getWilaya() ? $entity->getWilaya()->getNom() : '';
            });

        yield AssociationField::new('commune')
            ->setFormTypeOptions([
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez d\'abord une wilaya',
                'query_builder' => function(CommuneRepository $repo) {
                    return $repo->createQueryBuilder('c')
                        ->orderBy('c.nom', 'ASC');
                },
                'attr' => [
                    'data-widget' => 'commune-selector',
                ]
            ])
            ->formatValue(function ($value, $entity) {
                return $entity->getCommune() ? $entity->getCommune()->getNom() : '';
            })
            ->setFormTypeOption('attr', function ($value, $entity) {
                $attributes = [
                    'data-widget' => 'commune-selector',
                ];
                
                // Si nous sommes en mode édition et que l'entité a une commune
                if ($entity instanceof Bien && $entity->getCommune()) {
                    $attributes['data-selected-commune'] = $entity->getCommune()->getId();
                }
                
                return $attributes;
            });

        yield TextField::new('adresse', 'Adresse')->hideOnIndex();
        yield IntegerField::new('piece', 'Nombre de pièces');
        yield IntegerField::new('superficie', 'Superficie (m²)');
        yield IntegerField::new('etage', 'Étage');
        yield TextareaField::new('description', 'Description')->hideOnIndex();


        yield CollectionField::new('images')
            ->setEntryType(ImageFormType::class)
            ->setFormTypeOption('by_reference', false)
            ->onlyOnForms()
            ->setEntryIsComplex(true)
            ->setRequired(true)
            ->setHelp('Ajoutez jusqu\'à 15 images. La première image sera utilisée comme image principale.')
            ->setFormTypeOptions([
                'entry_options' => [
                    'attr' => [
                        'data-max-files' => 15
                    ]
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'delete_empty' => true,
                'attr' => [
                    'class' => 'images-collection',
                    'data-max-items' => 15
                ]
            ]);
    }
}