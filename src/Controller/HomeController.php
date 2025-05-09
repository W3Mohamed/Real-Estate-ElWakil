<?php

namespace App\Controller;

use App\Entity\Proposition;
use App\Entity\Reservation;
use App\Repository\BienRepository;
use App\Repository\ParamettreRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'accueil')]
    public function index(TypeRepository $typeRepository,
        ParamettreRepository $paramettreRepository,
        BienRepository $bienRepository): Response
    {
        $types = $typeRepository->findAll();

        $parametres = $paramettreRepository->find(1); // Récupère l'entrée avec id=1

        // Récupérer les biens séparés par type de transaction
        $biensALouer = $bienRepository->findBiensALouer();
        $biensAVendre = $bienRepository->findBiensAVendre();

        return $this->render('index.html.twig',[
            'types' => $types,
            'parametres' => $parametres,
            'biensALouer' => $biensALouer,
            'biensAVendre' => $biensAVendre
        ]);
    }

    #[Route('/biens', name: 'biens')]
    public function biens(Request $request, BienRepository $bienRepository, TypeRepository $typeRepository, ParamettreRepository $paramettreRepository): Response
    {
        $transaction = $request->query->get('t');
        $typeId = $request->query->get('type');
        $pieces = $request->query->get('pieces');
        $superficie = $request->query->get('superficie');
        $prix = $request->query->get('prix');

        $queryBuilder = $bienRepository->createQueryBuilder('b')
            ->leftJoin('b.images', 'i', 'WITH', 'i.id = (
                SELECT MIN(i2.id) FROM App\Entity\Images i2 
                WHERE i2.Bien = b.id
            )')
            ->addSelect('i') // Charge uniquement la première image
            ->orderBy('b.id', 'DESC');

        if ($transaction) {
            $queryBuilder->andWhere('b.transaction = :transaction')
                ->setParameter('transaction', $transaction);
        }
        
        if ($typeId) {
            $queryBuilder->andWhere('b.type = :typeId')
                ->setParameter('typeId', $typeId);
        }

        if ($pieces) {
            switch ($pieces) {
                case '1': // 1-2 pièces
                    $queryBuilder->andWhere('b.piece BETWEEN 1 AND 2');
                    break;
                case '2': // 3-4 pièces
                    $queryBuilder->andWhere('b.piece BETWEEN 3 AND 4');
                    break;
                case '3': // 5+ pièces
                    $queryBuilder->andWhere('b.piece >= 5');
                    break;
            }
        }
    
        // Filtre par superficie
        if ($superficie) {
            switch ($superficie) {
                case '1': // Moins de 50m²
                    $queryBuilder->andWhere('b.superficie < 50');
                    break;
                case '2': // 50m² - 100m²
                    $queryBuilder->andWhere('b.superficie BETWEEN 50 AND 100');
                    break;
                case '3': // 100m² - 200m²
                    $queryBuilder->andWhere('b.superficie BETWEEN 100 AND 200');
                    break;
                case '4': // Plus de 200m²
                    $queryBuilder->andWhere('b.superficie > 200');
                    break;
            }
        }
    
        // Filtre par prix
        if ($prix) {
            switch ($prix) {
                case '1': // Moins de 5M DZD
                    $queryBuilder->andWhere('b.prix < 5000000');
                    break;
                case '2': // 5M - 10M DZD
                    $queryBuilder->andWhere('b.prix BETWEEN 5000000 AND 10000000');
                    break;
                case '3': // 10M - 20M DZD
                    $queryBuilder->andWhere('b.prix BETWEEN 10000000 AND 20000000');
                    break;
                case '4': // Plus de 20M DZD
                    $queryBuilder->andWhere('b.prix > 20000000');
                    break;
            }
        }

        $biens = $queryBuilder->getQuery()->getResult();
        $query = $queryBuilder->getQuery();
    
        // Pagination
        $page = $request->query->getInt('page', 1);
        $limit = 9; // Nombre d'items par page
        
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);
        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $limit);
        
        $paginator
            ->getQuery()
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit
        $types = $typeRepository->findAll();
        $parametres = $paramettreRepository->find(1); 

        return $this->render('biens.html.twig',[
            'types' => $types,
            'parametres' => $parametres,
            'biens' => $biens,
            'currentTransaction' => $transaction,
            'biens' => $paginator,
            'currentPage' => $page,
            'pagesCount' => $pagesCount,
            'currentType' => $typeId,
            'currentPieces' => $pieces,
            'currentSuperficie' => $superficie,
            'currentPrix' => $prix
        ]);
    }

    // #[Route('/detail', name: 'detail')]
    // public function detail(TypeRepository $typeRepository,
    //  Request $request,
    //  ParamettreRepository $paramettreRepository,
    //  BienRepository $bienRepository): Response
    // {
    //     $bienId = $request->query->get('id');
    //     $bien = $bienRepository->findWithImages($bienId);

    //     if (!$bien) {
    //         throw $this->createNotFoundException('Le bien demandé n\'existe pas');
    //     }

    //     $similarBiens = $bienRepository->findSimilarBiens($bien);
    //     $types = $typeRepository->findAll();
    //     $parametres = $paramettreRepository->find(1); 

    //     return $this->render('detail.html.twig',[
    //         'bien' => $bien,
    //         'types' => $types,
    //         'parametres' => $parametres,
    //         'similarBiens' => $similarBiens
    //     ]);
    // }

    #[Route('/details', name: 'details')]
    public function details(TypeRepository $typeRepository,
    ParamettreRepository $paramettreRepository,
    BienRepository $bienRepository): Response
    {
        $types = $typeRepository->findAll();

        $parametres = $paramettreRepository->find(1);
        return $this->render('detail.html.twig',[
            'types' => $types,
            'parametres' => $parametres
        ]);
    }

    #[Route('/apropos', name: 'about')]
    public function about(TypeRepository $typeRepository,ParamettreRepository $paramettreRepository): Response
    {
        $types = $typeRepository->findAll();
        $parametres = $paramettreRepository->find(1); 
        return $this->render('apropos.html.twig',[
            'types' => $types,
            'parametres' => $parametres
        ]);
    }
}
