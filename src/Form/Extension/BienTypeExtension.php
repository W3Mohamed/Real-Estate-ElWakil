<?php

namespace App\Form\Extension;

use App\Entity\Bien;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class BienTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Vérifier si on traite un formulaire pour l'entité Bien
        if (!isset($options['data_class']) || $options['data_class'] !== Bien::class) {
            return;
        }

        // Gestion de la soumission du formulaire
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            
            if (isset($data['coordinates']) && !empty($data['coordinates'])) {
                // Séparer les coordonnées
                $coordinates = explode(',', $data['coordinates']);
                if (count($coordinates) === 2) {
                    $data['latitude'] = trim($coordinates[0]);
                    $data['longitude'] = trim($coordinates[1]);
                }
                // Supprimer le champ coordinates temporaire
                unset($data['coordinates']);
                $event->setData($data);
            }
        });

        // Préparer les données pour l'affichage
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $bien = $event->getData();
            $form = $event->getForm();
            
            if ($bien instanceof Bien && $bien->getLatitude() && $bien->getLongitude()) {
                // Préparer la valeur coordinates pour l'affichage
                $coordinates = $bien->getLatitude() . ',' . $bien->getLongitude();
                // Cette valeur sera utilisée par le template
            }
        });
    }
}