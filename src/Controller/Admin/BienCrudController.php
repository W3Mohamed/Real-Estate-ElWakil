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
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;

class BienCrudController extends AbstractCrudController
{
    private $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
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
            ->addJsFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js')
            ->addJsFile('js/coordinate-extractor.js');
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
        yield ChoiceField::new('papier')
            ->setChoices([
                'Acte de propriété' => 'Acte de propriété',
                'Livret foncier' => 'Livret foncier',
                'Acte et livret foncier' => 'Acte et livret foncier',
                'Autre' => 'Autre',
            ]);
        // yield NumberField::new('latitude', 'Latitude')
        //     ->hideOnIndex()
        //     ->setLabel(' ')
        //     ->setFormTypeOption('attr', ['style' => 'display: none;']); // Cache visuellement mais garde dans le form
            
        // yield NumberField::new('longitude', 'Longitude')
        //     ->hideOnIndex()
        //     ->setLabel(' ')
        //     ->setFormTypeOption('attr', ['style' => 'display: none;']); // Cache visuellement mais garde dans le form

        // NOUVEAU CHAMP URL GOOGLE MAPS
        yield TextField::new('googleMapsUrl', 'URL Google Maps')
        ->setHelp('Collez l\'URL Google Maps pour extraire automatiquement les coordonnées')
        ->setFormTypeOptions([
            'attr' => [
                'id' => 'google-maps-url',
                'placeholder' => 'https://www.google.com/maps/place/...',
                'class' => 'form-control'
            ]
        ])
        ->hideOnIndex();

        // Bouton pour extraire les coordonnées
        yield TextField::new('extractButton', 'Coordonnées')
        ->setFormTypeOptions([
            'mapped' => false,
            'attr' => [
                'style' => 'display: none;'
            ]
        ])
        ->setFormTypeOption('label', false)
        ->hideOnIndex()
        ->onlyOnForms();

        yield NumberField::new('latitude', 'Latitude')
        ->hideOnIndex()
        ->setFormTypeOptions([
            'attr' => [
                'id' => 'latitude-field',
                'readonly' => true,
                'style' => 'background-color: #f8f9fa;'
            ]
        ])
        ->setHelp('Automatiquement rempli depuis l\'URL Google Maps');

        yield NumberField::new('longitude', 'Longitude')
        ->hideOnIndex()
        ->setFormTypeOptions([
            'attr' => [
                'id' => 'longitude-field',
                'readonly' => true,
                'style' => 'background-color: #f8f9fa;'
            ]
        ])
        ->setHelp('Automatiquement rempli depuis l\'URL Google Maps');


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


    // Nouvelle méthode pour traiter les données avant la persistance
    public function persistEntity($entityManager, $entityInstance): void
    {
        $this->extractCoordinatesFromUrl($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity($entityManager, $entityInstance): void
    {
        $this->extractCoordinatesFromUrl($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function extractCoordinatesFromUrl(Bien $bien): void
    {
        $googleMapsUrl = $bien->getGoogleMapsUrl();
        
        if (empty($googleMapsUrl)) {
            return;
        }

        try {
            $this->logger->info('Extracting coordinates from URL', ['url' => $googleMapsUrl]);
            
            $httpClient = HttpClient::create([
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept-Language' => 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7'
                ],
                'timeout' => 30
            ]);

            // Si c'est une URL courte, la résoudre d'abord
            $finalUrl = $googleMapsUrl;
            if (strpos($googleMapsUrl, 'maps.app.goo.gl') !== false) {
                $response = $httpClient->request('GET', $googleMapsUrl, [
                    'max_redirects' => 10
                ]);
                $finalUrl = $response->getInfo('url');
                $this->logger->info('Resolved short URL', ['final_url' => $finalUrl]);
            }

            $coordinates = $this->extractCoordinatesFromGoogleUrl($finalUrl);
            
            if ($coordinates) {
                $bien->setLatitude($coordinates['lat']);
                $bien->setLongitude($coordinates['lng']);
                
                $this->logger->info('Coordinates extracted successfully', [
                    'latitude' => $coordinates['lat'],
                    'longitude' => $coordinates['lng']
                ]);
                
                // Optionnel: ajouter un message flash de succès
                $this->addFlash('success', sprintf(
                    'Coordonnées extraites avec succès: Lat: %s, Lng: %s',
                    $coordinates['lat'],
                    $coordinates['lng']
                ));
            } else {
                $this->logger->warning('Could not extract coordinates from URL', ['url' => $finalUrl]);
                $this->addFlash('warning', 'Impossible d\'extraire les coordonnées de cette URL');
            }

        } catch (\Exception $e) {
            $this->logger->error('Error extracting coordinates', [
                'error' => $e->getMessage(),
                'url' => $googleMapsUrl
            ]);
            $this->addFlash('error', 'Erreur lors de l\'extraction des coordonnées: ' . $e->getMessage());
        }
    }

    private function extractCoordinatesFromGoogleUrl(string $url): ?array
    {
        $decodedUrl = urldecode($url);
        
        // Pattern principal: @latitude,longitude
        if (preg_match('/@([-0-9.]+),([-0-9.]+)(?:,|z|m)/', $decodedUrl, $matches)) {
            return [
                'lat' => (float)$matches[1],
                'lng' => (float)$matches[2]
            ];
        }
        
        // Pattern alternatif: !3d!4d
        if (preg_match('/!3d([-0-9.]+)!4d([-0-9.]+)/', $decodedUrl, $matches)) {
            return [
                'lat' => (float)$matches[1],
                'lng' => (float)$matches[2]
            ];
        }
        
        // Pattern ll parameter
        if (preg_match('/ll=([-0-9.]+),([-0-9.]+)/', $decodedUrl, $matches)) {
            return [
                'lat' => (float)$matches[1],
                'lng' => (float)$matches[2]
            ];
        }
        
        return null;
    }

}