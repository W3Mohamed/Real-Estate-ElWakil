<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ReservationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Réservations')
            ->setPageTitle('detail', 'Détail réservation')
            ->setSearchFields(['nom', 'email', 'telephone', 'bien.libelle'])
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable('new', 'edit') // Désactive création et modification
            ->add('index', 'detail') // Ajoute le bouton "Voir"
            ->setPermission('delete', 'ROLE_ADMIN') // Restreint la suppression aux admins
            ->remove('detail', 'delete'); // Optionnel: supprime le bouton delete de la page detail
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom', 'Nom complet'),
            TextField::new('email'),
            TextField::new('telephone', 'Téléphone'),
            AssociationField::new('Bien')
                ->setLabel('Bien concerné')
                ->setCrudController(BienCrudController::class) // Assurez-vous d'importer le BienCrudController
                ->formatValue(function ($value, $entity) {
                    return $entity->getBien() ? $entity->getBien()->getLibelle() : 'Non spécifié';
                }),
            TextareaField::new('message')
                ->onlyOnDetail()
                ->setLabel('Message de la réservation')
                ->formatValue(function ($value) {
                    return nl2br($value); // Pour afficher correctement les sauts de ligne
                })
        ];
    }
}