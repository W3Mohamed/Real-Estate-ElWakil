<?php

namespace App\Controller\Admin;

use App\Entity\Proposition;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PropositionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Proposition::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Propositions')
            ->setPageTitle('detail', 'Détail proposition')
            ->setSearchFields(['nom', 'email'])
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable('new', 'edit')
            ->add('index', 'detail')
            ->setPermission('delete', 'ROLE_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom', 'Nom complet'),
            TextField::new('email'),
            TextField::new('telephone', 'Téléphone'),
            TextField::new('type.libelle', 'Type de bien') // Affiche directement le libellé
                ->setTemplatePath('bundles/type_libelle.html.twig'), // Optionnel pour un meilleur contrôle
            TextField::new('transaction'),
            TextField::new('adresse'),
            TextField::new('description')->onlyOnDetail(),
        ];
    }
}
