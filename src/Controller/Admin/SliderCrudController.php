<?php

namespace App\Controller\Admin;

use App\Entity\Slider;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class SliderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Slider::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Slide')
            ->setEntityLabelInPlural('Slider')
            ->setPageTitle('index', 'Gestion du slider')
            ->setPageTitle('new', 'Ajouter un nouveau slide')
            ->setPageTitle('edit', 'Modifier le slide')
            ->setDefaultSort(['ordre' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            ImageField::new('image', 'Image')
            ->setBasePath('uploads/slider/')
            ->setUploadDir('public/uploads/slider/')
            ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
            ->setRequired(false),    
            IntegerField::new('ordre', 'Ordre d\'affichage')
                ->setHelp('Définissez l\'ordre d\'apparition des slides')
        ];
    }
}