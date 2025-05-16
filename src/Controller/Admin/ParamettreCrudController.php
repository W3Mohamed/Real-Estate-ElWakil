<?php

namespace App\Controller\Admin;

use App\Entity\Paramettre;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ParamettreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Paramettre::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('edit', 'Modifier les paramètres')
            ->setPageTitle('index', 'Paramètres')
            ->setEntityLabelInSingular('Paramètres')
            // Désactive le bouton "Add" dans l'index
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions
            ->disable(Action::NEW, Action::DELETE, Action::BATCH_DELETE)
            // On modifie l'action EDIT existante plutôt que de la réajouter
            ->update(Action::INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit')->setLabel('Modifier');
            });

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
         return [
            IdField::new('id')->hideOnForm(),
            TextField::new('email', 'Email'),
            TextField::new('telephone', 'Téléphone'),
            TextField::new('facebook', 'Facebook'),
            TextField::new('instagram', 'Instagram'),
            TextField::new('youtube', 'Youtube'),
            TextField::new('tiktok', 'Tiktok'),
            TextField::new('adresse', 'Adresse'),
            TextareaField::new('horaires', 'Horaires'),
        ];
    }
    
}
