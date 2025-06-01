<?php
// src/Form/Type/MapType.php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MapType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('latitude', HiddenType::class)
            ->add('longitude', HiddenType::class);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['center_lat'] = $options['center_lat'];
        $view->vars['center_lng'] = $options['center_lng'];
        $view->vars['zoom'] = $options['zoom'];
        $view->vars['current_lat'] = $form->get('latitude')->getData();
        $view->vars['current_lng'] = $form->get('longitude')->getData();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'center_lat' => 48.8566,
            'center_lng' => 2.3522,
            'zoom' => 10,
            'data_class' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'map';
    }
}