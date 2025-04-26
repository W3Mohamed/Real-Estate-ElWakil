<?php

namespace App\Controller;

use App\Entity\Contact;
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

    #[Route('/vendre', name: 'vendre')]
    public function vendre(Request $request, EntityManagerInterface $entityManager, TypeRepository $typeRepository): Response
    {
        $types = $typeRepository->findAll();
        if ($request->isMethod('POST')) {
            // Récupération des données du formulaire
            $data = $request->request->all();
            
            // Création d'une nouvelle proposition
            $proposition = new Proposition();
            $proposition->setNom($data['name']);
            $proposition->setTelephone($data['phone']);
            $proposition->setEmail($data['email']);
            
            // Récupération du type depuis la base de données
            $type = $typeRepository->find($data['type']);
            $proposition->setType($type);
            
            $proposition->setTransaction($data['transaction']);
            $proposition->setAdresse($data['adresse']);
            $proposition->setDescription($data['message']);
            
            // Enregistrement en base de données
            $entityManager->persist($proposition);
            $entityManager->flush();
            
            // Redirection vers l'accueil avec un message de succès
            $this->addFlash('success', 'Votre proposition a bien été enregistrée !');
            return $this->redirectToRoute('accueil', ['_fragment' => 'vendre']);
        }
        
        // Si méthode GET, affiche le formulaire avec les types
        return $this->redirectToRoute('accueil', ['_fragment' => 'vendre']);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(TypeRepository $typeRepository,ParamettreRepository $paramettreRepository): Response
    {
        $types = $typeRepository->findAll();
        $parametres = $paramettreRepository->find(1); // Récupère l'entrée avec id=1

        return $this->render('contact.html.twig',[
            'types' => $types,
            'parametres' => $parametres
        ]);
    }

    #[Route('/contact/envoyer', name: 'contacter')]
    public function contacter(Request $request, EntityManagerInterface $entityManager, TypeRepository $typeRepository): Response
    {
        $data = $request->request->all();
         // Validation minimale
        if (empty($data['phone']) && empty($data['email'])) {
            $this->addFlash('error', 'Le N° de téléphone ou l\'email sont obligatoire');
            return $this->redirectToRoute('contact', ['_fragment' => 'contact-form']);
        }
    
        // Création et enregistrement du contact
        $contact = new Contact();
        $contact->setPrenom($data['firstname'])
                ->setNom($data['lastname'])
                ->setEmail($data['email'])
                ->setTelephone($data['phone'])
                ->setSujet($data['subject'])
                ->setMessage($data['message']);
    
        $entityManager->persist($contact);
        $entityManager->flush();
    
        // Redirection avec message de succès
        $this->addFlash('success', 'Votre message a bien été envoyé !');
        return $this->redirectToRoute('contact', ['_fragment' => 'contact-form']);
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
        $types = $typeRepository->findAll();
        $parametres = $paramettreRepository->find(1); 

        return $this->render('biens.html.twig',[
            'types' => $types,
            'parametres' => $parametres,
            'biens' => $biens,
            'currentTransaction' => $transaction,
            'currentType' => $typeId,
            'currentPieces' => $pieces,
            'currentSuperficie' => $superficie,
            'currentPrix' => $prix
        ]);
    }

    #[Route('/detail', name: 'detail')]
    public function detail(TypeRepository $typeRepository,
     Request $request,
     ParamettreRepository $paramettreRepository,
     BienRepository $bienRepository): Response
    {
        $bienId = $request->query->get('id');
        $bien = $bienRepository->find($bienId);

        if (!$bien) {
            throw $this->createNotFoundException('Le bien demandé n\'existe pas');
        }

        $types = $typeRepository->findAll();
        $parametres = $paramettreRepository->find(1); 
        return $this->render('detail.html.twig',[
            'bien' => $bien,
            'types' => $types,
            'parametres' => $parametres
        ]);
    }

    #[Route('/reserver', name: 'app_reservation', methods: ['POST'])]
    public function reserver(
        Request $request,
        BienRepository $bienRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer les données du formulaire
        $nom = $request->request->get('nom');
        $email = $request->request->get('email');
        $telephone = $request->request->get('tel'); // Notez que c'est 'tel' dans le formulaire
        $message = $request->request->get('message');
        $bienId = $request->request->get('bien');

        // Validation basique
        if (empty($nom) || empty($email) || empty($telephone) || empty($bienId)) {
            $this->addFlash('error', 'Veuillez remplir tous les champs obligatoires.');
            return $this->redirectToRoute('detail', ['id' => $bienId]);
        }

        // Trouver le bien
        $bien = $bienRepository->find($bienId);
        if (!$bien) {
            $this->addFlash('error', 'Le bien demandé n\'existe pas.');
            return $this->redirectToRoute('accueil');
        }

        // Créer la réservation
        $reservation = new Reservation();
        $reservation->setNom($nom);
        $reservation->setEmail($email);
        $reservation->setTelephone($telephone);
        $reservation->setMessage($message ?? ''); // Message optionnel
        $reservation->setBien($bien);

        // Enregistrer en base
        $entityManager->persist($reservation);
        $entityManager->flush();

        // Redirection avec message de succès
        $this->addFlash('success', 'Votre demande de réservation a bien été envoyée !');
        return $this->redirectToRoute('detail', ['id' => $bienId]);
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
