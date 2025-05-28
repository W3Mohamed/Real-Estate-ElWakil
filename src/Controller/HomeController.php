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
        BienRepository $bienRepository): Response
    {
        $types = $typeRepository->findAll();
        $sliders = $sliderRepository->findBy([], ['ordre' => 'ASC']);
        $parametres = $paramettreRepository->find(1); // Récupère l'entrée avec id=1
        $biens = $bienRepository->findLastEight();
        // Formater les prix pour chaque bien
        foreach ($biens as $bien) {
            $bien->formattedPrix = $this->formatPrixDZD($bien->getPrix());
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
        ParamettreRepository $paramettreRepository): Response
    {
        $searchQuery = $request->query->get('query');
        // Récupération des paramètres de filtrage
        $transaction = $request->query->get('t');
        $typeId = $request->query->get('type');
        $wilayaId = $request->query->get('wilaya');
        $commune = $request->query->get('commune');
        $priceMin = $request->query->get('price_min');
        $priceMax = $request->query->get('price_max');
        $areaMin = $request->query->get('area_min');
        $areaMax = $request->query->get('area_max');
    
        $queryBuilder = $bienRepository->createQueryBuilder('b')
            ->leftJoin('b.images', 'i') // Charge TOUTES les images associées
            ->addSelect('i') // Important pour éviter le N+1 problem
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
        }

        // Récupération des données pour les listes déroulantes
        $types = $typeRepository->findAll();
        $parametres = $paramettreRepository->find(1); 
        $wilayas = $wilayaRepository->findAll();
    
        $communes = [];
        if ($wilayaId) {
            $communes = $communeRepository->findBy(['wilaya' => $wilayaId]);
        }

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
            'currentPriceMin' => $priceMin,
            'currentPriceMax' => $priceMax,
            'currentAreaMin' => $areaMin,
            'currentAreaMax' => $areaMax,
            'communes' => $communes,
            'wilayas' => $wilayas
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
            'types' => $types,
            'parametres' => $parametres,
            'similarBiens' => $formatedSimilarBiens
        ]);
    }


    #[Route('/get-communes/{wilayaId}', name: 'get_communes')]
    public function getCommunes(int $wilayaId, CommuneRepository $communeRepository): JsonResponse
    {
        $communes = $communeRepository->findBy(['wilaya' => $wilayaId]);
        
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
    
        return $result . ' DZD';
    }
}
