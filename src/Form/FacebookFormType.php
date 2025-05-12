<?php
namespace App\Form;

use App\Entity\Facebook;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class FacebookFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lien', UrlType::class, [
                'label' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Url(),
                ],
                'attr' => [
                    'placeholder' => 'https://facebook.com/...',
                    'class' => 'facebook-link-input'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Facebook::class,
        ]);
    }
}