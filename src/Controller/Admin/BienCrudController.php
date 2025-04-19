<?php

namespace App\Controller\Admin;

use App\Entity\Bien;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


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
        yield TextField::new('transaction', 'Type de transaction');
        
        yield AssociationField::new('type', 'Type de bien')
            ->setFormTypeOption('choice_label', 'libelle')
            ->setFormTypeOption('query_builder', function (EntityRepository $er): QueryBuilder {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.libelle', 'ASC');
            });
    
        // Wilaya et Commune doivent venir avant CollectionField
        yield AssociationField::new('wilaya')
            ->setFormTypeOption('choice_label', 'nom')
            ->setFormTypeOption('query_builder', function (EntityRepository $er) {
                return $er->createQueryBuilder('w')
                    ->orderBy('w.id', 'ASC');
            });

        yield AssociationField::new('commune')
            ->setFormTypeOptions([
                'choices' => [],
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez d\'abord une wilaya',
                'attr' => [
                    'data-widget' => 'commune-selector',
                    'disabled' => true
                ]
            ]);

        yield TextField::new('adresse', 'Adresse')->hideOnIndex();
        yield IntegerField::new('piece', 'Nombre de pièces');
        yield IntegerField::new('superficie', 'Superficie (m²)');
        yield IntegerField::new('etage', 'Étage');
        yield TextareaField::new('description', 'Description')->hideOnIndex();
    
        // yield ChoiceField::new('equipementsField', 'Équipements')
        // ->setFormType(ChoiceType::class)
        // ->setFormTypeOptions([
        //     'choices' => $this->getEquipementChoices(),
        //     'multiple' => true,
        //     'expanded' => true, // Pour avoir des checkboxes
        //     'required' => false,
        //     'mapped' => false // Ce champ n'est pas lié directement à l'entité
        // ])
        // ->formatValue(function ($value, Bien $bien) {
        //     // Affiche les équipements dans l'index
        //     return implode(', ', $bien->getEquipements()->map(fn($e) => $e->getEquipement())->toArray());
        // });
        

    }
    private function getEquipementChoices(): array
    {
        return [
            'Piscine chauffée' => 'Piscine chauffée',
            'Wifi haut débit' => 'Wifi haut débit',
            'Climatisation' => 'Climatisation',
            'Jardin paysager' => 'Jardin paysager',
            'Système de sécurité' => 'Système de sécurité',
            'Chauffage central' => 'Chauffage central',
            'Smart Home' => 'Smart Home',
            'Garage' => 'Garage',
            'Cave' => 'Cave',
            // Ajoutez d'autres équipements si nécessaire
        ];
    }
}