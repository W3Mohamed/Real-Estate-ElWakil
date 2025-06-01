<?php

namespace App\Controller\Admin;

use App\Entity\Bien;
use App\Form\FacebookFormType;
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
use App\Form\ImageFormType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class BienCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Bien::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Bien')
            ->setEntityLabelInPlural('Biens')
            ->setPageTitle('edit', fn (Bien $bien) => sprintf('Modifier le bien %s', $bien->getLibelle()))
            ->setPageTitle('new', 'Ajouter un bien')
            ->setPageTitle('index', 'Liste des biens')
            ->setPageTitle('detail', fn (Bien $bien) => sprintf('Détails du bien %s', $bien->getLibelle()))
            ->setDefaultSort(['id' => 'DESC'])
            ->overrideTemplates([
                'crud/edit' => 'admin/bien/edit.html.twig',
                'crud/new' => 'admin/bien/new.html.twig'
            ]);
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            // Ajout de Leaflet CSS et JS
            ->addCssFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css')
            ->addJsFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('libelle', 'Libellé');
        yield IntegerField::new('prix', 'Prix'); 
        yield TextField::new('telephone', 'N° Téléphone');
        yield TextField::new('whatsapp', 'WhatsApp')->hideOnIndex();
        yield TextField::new('viber', 'Viber')->hideOnIndex();
        yield TextField::new('telegram', 'Telegram')->hideOnIndex();
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
                }
            ])
            // Supprimez les autres options qui pourraient interférer
            ->formatValue(function ($value, $entity) {
                return $entity->getCommune() ? $entity->getCommune()->getNom() : '';
            });


        yield TextareaField::new('adresse', 'Adresse')->hideOnIndex();
      
        // yield TextField::new('latitude', 'Latitude')
        //     ->hideOnForm()
        //     ->hideOnIndex();
            
        // yield TextField::new('longitude', 'Longitude')
        //     ->hideOnForm()
        //     ->hideOnIndex();

        yield NumberField::new('latitude', 'Latitude')
            ->hideOnIndex()
            ->setLabel(' ')
            ->setFormTypeOption('attr', ['style' => 'display: none;']); // Cache visuellement mais garde dans le form
            
        yield NumberField::new('longitude', 'Longitude')
            ->hideOnIndex()
            ->setLabel(' ')
            ->setFormTypeOption('attr', ['style' => 'display: none;']); // Cache visuellement mais garde dans le form

        yield IntegerField::new('piece', 'Nombre de pièces')->hideOnIndex();
        yield IntegerField::new('bain', 'Salle de bain')->hideOnIndex();
        yield IntegerField::new('superficie', 'Superficie (m²)')->hideOnIndex();
        yield IntegerField::new('etage', 'Étage')->hideOnIndex();
        yield TextareaField::new('description', 'Description')->hideOnIndex();
        yield TextareaField::new('youtube', 'Lien youtube')->hideOnIndex();
        yield TextareaField::new('insta', 'Lien instagram')->hideOnIndex();
        yield TextareaField::new('tiktok', 'Lien tiktok')->hideOnIndex();

        yield CollectionField::new('facebooks', 'Liens Facebook')
            ->setEntryType(FacebookFormType::class)
            ->setFormTypeOption('by_reference', false)
            ->onlyOnForms()
            ->setEntryIsComplex(true)
            ->setRequired(false)
            ->setHelp('Ajoutez plusieurs liens Facebook (vidéos, posts, etc.)')
            ->setFormTypeOptions([
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'delete_empty' => true,
                'attr' => [
                    'class' => 'facebook-links-collection',
                ]
            ]);


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