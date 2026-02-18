<?php

namespace App\Controller;

use App\Entity\Proposition;
use App\Entity\Reservation;
use App\Entity\Wilaya;
use App\Repository\BienRepository;
use App\Repository\CommuneRepository;
use App\Repository\ParamettreRepository;
use App\Repository\SliderRepository;
use App\Repository\TypeRepository;
use App\Repository\WilayaRepository;
use App\Service\BienMatchingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'accueil')]
    public function index(TypeRepository $typeRepository,
        ParamettreRepository $paramettreRepository,
        SliderRepository $sliderRepository,
        BienRepository $bienRepository,
        BienMatchingService $bienMatching): Response
    {
        $types = $typeRepository->findAll();
        $sliders = $sliderRepository->findBy([], ['ordre' => 'ASC']);
        $parametres = $paramettreRepository->find(1); // Récupère l'entrée avec id=1
        $biens = $bienRepository->findLastEight();
        // Formater les prix pour chaque bien
        foreach ($biens as $bien) {
            $bien->formattedPrix = $this->formatPrixDZD($bien->getPrix());
            $bien->nbAcheteurs = count($bienMatching->findPotentialClientsForBien($bien));
        }
        // Récupérer les biens séparés par type de transaction
        $biensALouer = $bienRepository->findBiensALouer();
        $biensAVendre = $bienRepository->findBiensAVendre();

        return $this->render('index.html.twig',[
            'types' => $types,
            'biens' => $biens,
            'sliders' => $sliders,
            'parametres' => $parametres,
            'biensALouer' => $biensALouer,
            'biensAVendre' => $biensAVendre
        ]);
    }

    #[Route('/biens', name: 'biens')]
    public function biens(Request $request, 
        BienRepository $bienRepository, WilayaRepository $wilayaRepository,
        TypeRepository $typeRepository, CommuneRepository $communeRepository,
        ParamettreRepository $paramettreRepository,
        BienMatchingService $bienMatching): Response
    {
        $searchQuery = $request->query->get('query');
        // Récupération des paramètres de filtrage
        $transaction = $request->query->get('t');
        $typeId = (int)$request->query->get('type');
        $wilayaId = (int)$request->query->get('wilaya');
        $commune = (int)$request->query->get('commune');
        $papier = $request->query->get('papier');
        $priceMin = (int)$request->query->get('price_min') ?? null;
        $priceMax = (int)$request->query->get('price_max') ?? null;
        $areaMin = (int)$request->query->get('area_min') ?? null;
        $areaMax = (int)$request->query->get('area_max') ?? null;
    
        $queryBuilder = $bienRepository->createQueryBuilder('b')
            ->leftJoin('b.images', 'i') // Charge TOUTES les images associées
            ->leftJoin('b.facebooks', 'f')
            ->addSelect('i')
            ->where('i.id IS NOT NULL') // Filtre les biens avec images
            ->andWhere('(b.youtube IS NOT NULL OR b.insta IS NOT NULL OR b.tiktok IS NOT NULL OR f.id IS NOT NULL)')  // Important pour éviter le N+1 problem
            ->orderBy('b.id', 'DESC');
    
        if ($searchQuery) {
            $queryBuilder->andWhere('b.libelle LIKE :query OR b.description LIKE :query OR b.telephone LIKE :query')
                ->setParameter('query', '%'.$searchQuery.'%');
        }

        // Filtre par transaction (vente/location)
        if ($transaction) {
            $queryBuilder->andWhere('b.transaction = :transaction')
                ->setParameter('transaction', $transaction);
        }
        
        // Filtre par type de bien
        if ($typeId) {
            $queryBuilder->andWhere('b.type = :typeId')
                ->setParameter('typeId', $typeId);
        }
    
        // Filtre par wilaya
        if ($wilayaId) {
            $queryBuilder->andWhere('b.wilaya = :wilayaId')
                ->setParameter('wilayaId', $wilayaId);
        }
    
        // Filtre par commune
        if ($commune) {
            $queryBuilder->andWhere('b.commune = :commune')
                ->setParameter('commune', $commune);
        }
    
        // Filtre par papier
        if ($papier) {
            $queryBuilder->andWhere('b.papier = :papier')
                ->setParameter('papier', $papier);
        }

        // Filtre par plage de prix
        if ($priceMin || $priceMax) {
            if ($priceMin && $priceMax) {
                $queryBuilder->andWhere('b.prix BETWEEN :priceMin AND :priceMax')
                    ->setParameter('priceMin', $priceMin)
                    ->setParameter('priceMax', $priceMax);
            } elseif ($priceMin) {
                $queryBuilder->andWhere('b.prix >= :priceMin')
                    ->setParameter('priceMin', $priceMin);
            } elseif ($priceMax) {
                $queryBuilder->andWhere('b.prix <= :priceMax')
                    ->setParameter('priceMax', $priceMax);
            }
        }
    
        // Filtre par plage de superficie
        if ($areaMin || $areaMax) {
            if ($areaMin && $areaMax) {
                $queryBuilder->andWhere('b.superficie BETWEEN :areaMin AND :areaMax')
                    ->setParameter('areaMin', $areaMin)
                    ->setParameter('areaMax', $areaMax);
            } elseif ($areaMin) {
                $queryBuilder->andWhere('b.superficie >= :areaMin')
                    ->setParameter('areaMin', $areaMin);
            } elseif ($areaMax) {
                $queryBuilder->andWhere('b.superficie <= :areaMax')
                    ->setParameter('areaMax', $areaMax);
            }
        }
    
        // Récupération des résultats non paginés pour le comptage
        // $biens = $queryBuilder->getQuery()->getResult();
        // $query = $queryBuilder->getQuery();
    
        // Pagination
        $page = $request->query->getInt('page', 1);
        $limit = 12; // Nombre d'items par page
        
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($queryBuilder);
        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $limit);
        
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit

        // Formatage des prix pour l'affichage
        $biens = [];
        foreach ($paginator as $bien) {
            $bien->formattedPrix = $this->formatPrixDZD($bien->getPrix());
            $biens[] = $bien;
            $bien->nbAcheteurs = count($bienMatching->findPotentialClientsForBien($bien));
        }

        // Récupération des données pour les listes déroulantes
        $types = $typeRepository->findAll();
        $parametres = $paramettreRepository->find(1); 
        // par ordre alphabétique
        $wilayas = $wilayaRepository->findBy([], ['nom' => 'ASC']);

        $communes = [];
        if ($wilayaId) {
            $communes = $communeRepository->findBy(['wilaya' => $wilayaId],['nom' => 'ASC']);
        }
        // jusqu'à 20 milliards
        $priceOptions = [
            ['label' => '300 millions', 'value' => '3000000'],
            ['label' => '600 millions', 'value' => '6000000'],
            ['label' => '900 millions', 'value' => '9000000'],
            ['label' => '1.2 milliard', 'value' => '12000000'],
            ['label' => '1.5 milliard', 'value' => '15000000'],
            ['label' => '1.8 milliard', 'value' => '18000000'],
            ['label' => '2.1 milliards', 'value' => '21000000'],
            ['label' => '2.4 milliards', 'value' => '24000000'],
            ['label' => '2.7 milliards', 'value' => '27000000'],
            ['label' => '3 milliards', 'value' => '30000000'],
            ['label' => '3.5 milliards', 'value' => '35000000'],
            ['label' => '4 milliards', 'value' => '40000000'],
            ['label' => '4.5 milliards', 'value' => '45000000'],
            ['label' => '5 milliards', 'value' => '50000000'],
            ['label' => '5.5 milliards', 'value' => '55000000'],
            ['label' => '6 milliards', 'value' => '60000000'],
            ['label' => '6.5 milliards', 'value' => '65000000'],
            ['label' => '7 milliards', 'value' => '70000000'],
            ['label' => '7.5 milliards', 'value' => '75000000'],
            ['label' => '8 milliards', 'value' => '80000000'],
            ['label' => '8.5 milliards', 'value' => '85000000'],
            ['label' => '9 milliards', 'value' => '90000000'],
            ['label' => '9.5 milliards', 'value' => '95000000'],
            ['label' => '10 milliards', 'value' => '100000000'],
            ['label' => '11 milliards', 'value' => '110000000'],
            ['label' => '12 milliards', 'value' => '120000000'],
            ['label' => '13 milliards', 'value' => '130000000'],
            ['label' => '14 milliards', 'value' => '140000000'],
            ['label' => '15 milliards', 'value' => '150000000'],
            ['label' => '16 milliards', 'value' => '160000000'],
            ['label' => '17 milliards', 'value' => '170000000'],
            ['label' => '18 milliards', 'value' => '180000000'],
            ['label' => '19 milliards', 'value' => '190000000'],
            ['label' => '20 milliards et plus', 'value' => '200000000-9999999999']
        ];
        // jusqu'à 20 hektares
        $areaOptions = [
            ['label' => '50 m²', 'value' => '50'],
            ['label' => '100 m²', 'value' => '100'],
            ['label' => '200 m²', 'value' => '200'],
            ['label' => '300 m²', 'value' => '300'],
            ['label' => '400 m²', 'value' => '400'],
            ['label' => '500 m²', 'value' => '500'],
            ['label' => '600 m²', 'value' => '600'],
            ['label' => '700 m²', 'value' => '700'],
            ['label' => '800 m²', 'value' => '800'],
            ['label' => '900 m²', 'value' => '900'],
            ['label' => '0.1 hectares', 'value' => '1000'],
            ['label' => '0.2 hectares', 'value' => '2000'],
            ['label' => '0.3 hectares', 'value' => '3000'],
            ['label' => '0.4 hectares', 'value' => '4000'],
            ['label' => '0.5 hectares', 'value' => '5000'],
            ['label' => '0.6 hectares', 'value' => '6000'],
            ['label' => '0.7 hectares', 'value' => '7000'],
            ['label' => '0.8 hectares', 'value' => '8000'],
            ['label' => '0.9 hectares', 'value' => '9000'],
            ['label' => '1 hectare', 'value' => '10000'],
            ['label' => '2 hectares', 'value' => '20000'],
            ['label' => '3 hectares', 'value' => '30000'],
            ['label' => '4 hectares', 'value' => '40000'],
            ['label' => '5 hectares', 'value' => '50000'],
            ['label' => '6 hectares', 'value' => '60000'],
            ['label' => '7 hectares', 'value' => '70000'],
            ['label' => '8 hectares', 'value' => '80000'],
            ['label' => '9 hectares', 'value' => '90000'],
            ['label' => '10 hectares', 'value' => '100000'],
            ['label' => '11 hectares', 'value' => '110000'],
            ['label' => '12 hectares', 'value' => '120000'],
            ['label' => '13 hectares', 'value' => '130000'],
            ['label' => '14 hectares', 'value' => '140000'],
            ['label' => '15 hectares', 'value' => '150000'],
            ['label' => '16 hectares', 'value' => '160000'],
            ['label' => '17 hectares', 'value' => '170000'],
            ['label' => '18 hectares', 'value' => '180000'],
            ['label' => '19 hectares', 'value' => '190000'],
            ['label' => '20 hectares et plus', 'value' => '200000']
        ];

        return $this->render('biens.html.twig',[
            'types' => $types,
            'parametres' => $parametres,
            'biens' => $biens,
            'currentTransaction' => $transaction,
            'totalItems' => $totalItems,
            'paginator' => $paginator,
            'currentPage' => $page,
            'pagesCount' => $pagesCount,
            'currentType' => $typeId,
            'currentWilaya' => $wilayaId,
            'currentCommune' => $commune,
            'currentPapier' => $papier,
            'currentPriceMin' => $priceMin,
            'currentPriceMax' => $priceMax,
            'currentAreaMin' => $areaMin,
            'currentAreaMax' => $areaMax,
            'communes' => $communes,
            'wilayas' => $wilayas,
            'priceOptions' => $priceOptions,
            'areaOptions' => $areaOptions
        ]);
    }

    #[Route('/detail', name: 'detail')]
    public function detail(TypeRepository $typeRepository,
    Request $request,
    ParamettreRepository $paramettreRepository,
    BienRepository $bienRepository): Response
    {
        $bienId = $request->query->get('id');
        $types = $typeRepository->findAll();
        $bien = $bienRepository->findWithImages($bienId);
        $formatedPrix = $this->formatPrixDZD($bien->getPrix());
        $formatedPrixMap = $this->formatPrixMap($bien->getPrix());
        if (!$bien) {
            throw $this->createNotFoundException('Le bien demandé n\'existe pas');
        }
        $parametres = $paramettreRepository->find(1);

        if($bien->getTransaction() == 'vente') {
            $similarBiens = $bienRepository->findSimilarVenteBiens($bien, 3);
        } else {
            $similarBiens = $bienRepository->findSimilarLocationBiens($bien, 3);
        }

        // Formater les prix des biens similaires
        $formatedSimilarBiens = [];
        foreach ($similarBiens as $similarBien) {
            $formatedSimilarBien = [
                'entity' => $similarBien,
                'formatedPrix' => $this->formatPrixDZD($similarBien->getPrix())
            ];
            $formatedSimilarBiens[] = $formatedSimilarBien;
        }

        return $this->render('detail.html.twig', [
            'bien' => $bien,
            'prix' => $formatedPrix,
            'prixMap' => $formatedPrixMap,
            'types' => $types,
            'parametres' => $parametres,
            'similarBiens' => $formatedSimilarBiens
        ]);
    }

    #[Route('/api/biens', name: 'api_biens', methods: ['GET'])]
    public function getBiens(BienRepository $bienRepository): JsonResponse
    {
        // Récupérer tous les biens qui ont des coordonnées
        $biens = $bienRepository->createQueryBuilder('b')
            ->where('b.latitude IS NOT NULL')
            ->andWhere('b.longitude IS NOT NULL')
            ->andWhere('b.latitude != 0')
            ->andWhere('b.longitude != 0')
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($biens as $bien) {
            $data[] = [
                'id' => $bien->getId(),
                'libelle' => $bien->getLibelle(),
                'prix' => $this->formatPrixMap($bien->getPrix()),
                'adresse' => $bien->getAdresse(),
                'latitude' => (float) $bien->getLatitude(),
                'longitude' => (float) $bien->getLongitude(),
                'type' => $bien->getType() ? $bien->getType()->getLibelle() : null
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/get-communes/{wilayaId}', name: 'get_communes')]
    public function getCommunes(int $wilayaId, CommuneRepository $communeRepository): JsonResponse
    {
        $communes = $communeRepository->findBy(['wilaya' => $wilayaId],['nom' => 'ASC']);
        
        if (empty($communes)) {
            return new JsonResponse([], 404);
        }
        
        $response = [];
        foreach ($communes as $commune) {
            $response[] = [
                'id' => $commune->getId(),
                'nom' => $commune->getNom(),
                'code_postal' => $commune->getCodePostal()
            ];
        }
        
        return new JsonResponse($response);
    }

    private function formatPrixDZD(?int $prixCentimes): string
    {
        if ($prixCentimes === null) {
            return 'Prix non disponible';
        }
    
        // Convertir les centimes en unités standard (1 million = 10000 centimes)
        $prixStandard = $prixCentimes * 100; // 10000 centimes = 1 million DZD
        
        $milliards = floor($prixStandard / 1000000000);
        $reste = $prixStandard % 1000000000;
        $millions = floor($reste / 1000000);
        $milliers = floor(($reste % 1000000) / 1000);
        $unites = $reste % 1000;
    
        $result = '';
        
        if ($milliards > 0) {
            $result .= number_format($milliards, 0, ',', ' ') . ' Milliard' . ($milliards > 1 ? 's' : '');
        }
        
        if ($millions > 0) {
            if (!empty($result)) {
                $result .= ' ';
            }
            $result .= number_format($millions, 0, ',', ' ') . ' Million' . ($millions > 1 ? 's' : '');
        }
        
        if ($milliers > 0) {
            if (!empty($result)) {
                $result .= ' ';
            }
            $result .= number_format($milliers, 0, ',', ' ') . ' Mille';
        }
        
        if ($unites > 0 && empty($result)) {
            $result .= number_format($unites, 0, ',', ' ');
        }
    
        if (empty($result)) {
            return '0 DZD';
        }
    
        return $result;
    } 
    
    private function formatPrixMap(?int $prixCentimes): string
    {
        if ($prixCentimes === null) {
            return 'Prix non disponible';
        }
    
        // Convertir les centimes en unités standard (1 million = 10000 centimes)
        $prixStandard = $prixCentimes * 100; // 10000 centimes = 1 million DZD
        
        $milliards = floor($prixStandard / 1000000000);
        $reste = $prixStandard % 1000000000;
        $millions = floor($reste / 1000000);
        $milliers = floor(($reste % 1000000) / 1000);
        $unites = $reste % 1000;
    
        $result = '';
        
        if ($milliards > 0) {
            $result .= number_format($milliards, 0, ',', ' ') . ' Md';
        }
        
        if ($millions > 0) {
            if (!empty($result)) {
                $result .= ' ';
            }
            $result .= number_format($millions, 0, ',', ' ') . ' M';
        }
        
        if ($milliers > 0) {
            if (!empty($result)) {
                $result .= ' ';
            }
            $result .= number_format($milliers, 0, ',', ' ') . ' Mille';
        }
        
        if ($unites > 0 && empty($result)) {
            $result .= number_format($unites, 0, ',', ' ');
        }
    
        if (empty($result)) {
            return '0 DZD';
        }
    
        return $result . ' DZD';
    }
}
