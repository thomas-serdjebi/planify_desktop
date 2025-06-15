<?php

namespace App\Controller;

use App\Entity\Trajet;
use App\Entity\Tournee;
use App\Form\TourneeType;
use App\Service\GeolocationService;
use App\Repository\TrajetRepository;
use App\Repository\TourneeRepository;
use App\Repository\LivraisonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TourneeController extends AbstractController
{
    #[Route('/tournee', name: 'app_tournee_index', methods: ['GET'])]
    public function index(
        TourneeRepository $tourneeRepository,

    ): Response {
        $tournees = $tourneeRepository->findAll();

        // Organiser les trajets par tournée
        $tourneeTrajetsMap = [];

        foreach ($tournees as $tournee) {
            // Filtrer les trajets de cette tournée, triés par ordre
            $trajets = $tournee->getTrajets()->toArray();

            usort($trajets, function ($a, $b) {
                return $a->getOrdre() <=> $b->getOrdre();
            });

            // Associer la livraison au trajet (pour adresse d’arrivée)
            $trajetInfos = [];
                
            $adresseDepart = "Entrepôt";
            foreach ($trajets as $trajet) {
                $livraison = $trajet->getLivraison();
                $trajetInfos[] = [
                    'ordre' => $trajet->getOrdre(),
                    'duree' => $trajet->getDuree(),
                    'distance' => $trajet->getDistance(),
                    'adresseArrivee' => $livraison ? $livraison->getAdresse() : 'Inconnue',
                    'adresseDepart' => $adresseDepart, 
                    'statutLivraison' => $livraison ? $livraison->getStatut() : 'Non défini', // Ajout du statut de la livraison
                ];
                $adresseDepart = $livraison ? $livraison->getAdresse() : 'Inconnue'; // Mettre à jour l'adresse départ
            }

            $tourneeTrajetsMap[$tournee->getId()] = $trajetInfos;
        }

        return $this->render('tournee/index.html.twig', [
            'tournees' => $tournees,
            'tourneeTrajets' => $tourneeTrajetsMap
        ]);
    }



    #[Route('tournees/create', name: 'app_tournees_create')]
    public function tourneesCreate(
        EntityManagerInterface $entityManager,
        Request $request,
        LivraisonRepository $repo,
        GeolocationService $geolocationService
    ) {
        $form = $this->createForm(TourneeType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $date = $data['date'];
            $creneau = $data['creneau'];
    
            // Récupération des livraisons
            $livraisons = $repo->findLivraisonsByDateAndCreneau($date, $creneau);
    
            // Point de départ (entrepôt)
            $origin = ['lat' => 43.3432064, 'lng' => 5.434737999999999];
    
            // Construction des points de livraison
            $points = [];
            foreach ($livraisons as $livraison) {
                $lat = $livraison->getLatitude();
                $lng = $livraison->getLongitude();
                if ($lat !== null && $lng !== null) {
                    $points[] = [
                        'lat' => $lat,
                        'lng' => $lng,
                        'livraison' => $livraison,
                    ];
                }
            }
    
            // Paramètre : nombre de tournées
            $nbTournees = 2;
    
            // Recherche de la meilleure combinaison possible
            $tournees = $this->findBestPartition($points, $nbTournees, 10, $origin, $geolocationService);

    
            foreach ($tournees as $index => $tournee) {
                $tourneeEntity = new Tournee();
                $tourneeEntity->setDate($date);
                $tourneeEntity->setCreneau($creneau);
                $tourneeEntity->setStatut('programmé');
                
                $totalDistance = 0;
                $totalDuration = 0;
                $current = $origin;
            
                foreach ($tournee as $ordre => $point) {
                    // Calcul du trajet entre le point précédent et ce point
                    $distanceInfo = $geolocationService->getDistanceBetween(
                        $current['lat'] . ',' . $current['lng'],
                        $point['lat'] . ',' . $point['lng']
                    );
                    $distance = $distanceInfo['distance'];
                    $duration = $distanceInfo['duration'];
            
                    // Création du trajet
                    $trajet = new Trajet();
                    $trajet->setTournee($tourneeEntity);
                    $trajet->setOrdre($ordre + 1);
                    $trajet->setDistance($distance);
                    $trajet->setDuree($duration);
                    $trajet->setLivraison($point['livraison']);
                    $trajet->getLivraison()->setStatut('Programmée');
            
                    // Association dans la livraison
                    $point['livraison']->setTrajet($trajet);
            
                    $entityManager->persist($trajet);
            
                    // Mise à jour des compteurs
                    $totalDistance += $distance;
                    $totalDuration += $duration;
                    $current = $point;
                }
            
                $tourneeEntity->setDistance($totalDistance);
                $tourneeEntity->setDuree($totalDuration);
                $entityManager->persist($tourneeEntity);
            }
            
            // Enregistrement final en base
            $entityManager->flush();
            
        }
    
        return $this->render('tournee/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    private function findBestPartition(array $points, int $k, int $iterations, array $origin, GeolocationService $geolocationService): array
    {
        $bestPartition = [];
        $bestScore = null;
    
        for ($i = 0; $i < $iterations; $i++) {
            $clusters = $this->kMeansPartition($points, $k);
    
            $tournees = [];
            $totalDuration = 0;
    
            foreach ($clusters as $cluster) {
                $tournee = $this->optimiserOrdreLivraisons($cluster, $origin, $geolocationService);
                $tournees[] = $tournee;
    
                // Calcul de la durée totale pour cette tournée
                $current = $origin;
                foreach ($tournee as $point) {
                    $duration = $geolocationService->getDistanceBetween(
                        $current['lat'] . ',' . $current['lng'],
                        $point['lat'] . ',' . $point['lng']
                    )['duration'];
                    $totalDuration += $duration;
                    $current = $point;
                }
            }
    
            // Penalité d'équilibrage : variance sur le nombre de points par cluster
            $balancePenalty = $this->computeBalancePenalty($clusters);
    
            // Score global avec pondération de la pénalité (poids ajustable)
            $score = $totalDuration + $balancePenalty * 120;
    
            if ($bestScore === null || $score < $bestScore) {
                $bestScore = $score;
                $bestPartition = $tournees;
            }
        }
    
        return $bestPartition;
    }
    
    private function computeBalancePenalty(array $clusters): float
    {
        $sizes = array_map('count', $clusters);
        $mean = array_sum($sizes) / count($sizes);
        $variance = array_sum(array_map(fn($s) => pow($s - $mean, 2), $sizes)) / count($sizes);
        return $variance;
    }
    

    private function kMeansPartition(array $points, int $k, int $maxIterations = 10): array
    {
        shuffle($points);
        $centroids = array_slice($points, 0, $k);
    
        for ($iter = 0; $iter < $maxIterations; $iter++) {
            $clusters = array_fill(0, $k, []);
    
            foreach ($points as $point) {
                $minDist = null;
                $closest = 0;
                foreach ($centroids as $i => $centroid) {
                    $dist = pow($point['lat'] - $centroid['lat'], 2) + pow($point['lng'] - $centroid['lng'], 2);
                    if ($minDist === null || $dist < $minDist) {
                        $minDist = $dist;
                        $closest = $i;
                    }
                }
                $clusters[$closest][] = $point;
            }
    
            $newCentroids = [];
            foreach ($clusters as $cluster) {
                if (count($cluster) === 0) continue;
                $sumLat = array_sum(array_column($cluster, 'lat'));
                $sumLng = array_sum(array_column($cluster, 'lng'));
                $newCentroids[] = [
                    'lat' => $sumLat / count($cluster),
                    'lng' => $sumLng / count($cluster),
                ];
            }
    
            if (count($newCentroids) < $k) break;
            $centroids = $newCentroids;
        }
    
        return $clusters;
    }
        
    
    // Fonction utilitaire : optimisation d'une tournée (greedy TSP)
    private function optimiserOrdreLivraisons(array $points, array $origin, GeolocationService $geolocationService): array
    {
        $ordered = [];
        $current = $origin;
        $remaining = $points;
    
        while (count($remaining) > 0) {
            $minDist = null;
            $next = null;
            foreach ($remaining as $i => $point) {
                $dist = $geolocationService->getDistanceBetween(
                    $current['lat'] . ',' . $current['lng'],
                    $point['lat'] . ',' . $point['lng']
                )['duration'];
    
                if ($minDist === null || $dist < $minDist) {
                    $minDist = $dist;
                    $next = $i;
                }
            }
    
            $ordered[] = $remaining[$next];
            $current = $remaining[$next];
            unset($remaining[$next]);
            $remaining = array_values($remaining);
        }
    
        return $ordered;
    }
    
    

    #[Route('/api/tournees/passees/{livreurId}', name: 'tournees_passees')]
    public function tourneesPassees(EntityManagerInterface $entityManager, int $livreurId): JsonResponse
    {
        $aujourdhui = new \DateTimeImmutable('today');

        $tournees = $entityManager->getRepository(Tournee::class)->createQueryBuilder('t')
            ->leftJoin('t.livraisons', 'l')
            ->addSelect('l')
            ->where('t.date < :aujourdhui') 
            ->andWhere('t.livreur = :livreurId')
            ->setParameter('aujourdhui', $aujourdhui->setTime(0, 0, 0)) 
            ->setParameter('livreurId', $livreurId)
            ->getQuery()
            ->getResult();

        return new JsonResponse($this->normalizeTournees($tournees));
    }

    #[Route('/api/tournees/aujourdhui/{livreurId}', name: 'tournees_aujourdhui')]
    public function tourneesDuJour(EntityManagerInterface $entityManager, int $livreurId): JsonResponse
    {
        $aujourdhui = new \DateTimeImmutable('today');

        $tournees = $entityManager->getRepository(Tournee::class)->createQueryBuilder('t')
            ->leftJoin('t.livraisons', 'l')
            ->addSelect('l')
            ->where('t.date BETWEEN :start AND :end')
            ->andWhere('t.livreur = :livreurId')
            ->setParameter('start', $aujourdhui->setTime(0, 0, 0))
            ->setParameter('end', $aujourdhui->setTime(23, 59, 59))
            ->setParameter('livreurId', $livreurId)
            ->getQuery()
            ->getResult();

        return new JsonResponse($this->normalizeTournees($tournees));
    }

    #[Route('/api/tournees/futures/{livreurId}', name: 'tournees_futures')]
    public function tourneesFutures(EntityManagerInterface $entityManager, int $livreurId): JsonResponse
    {
        $aujourdhui = new \DateTimeImmutable('today');

        $tournees = $entityManager->getRepository(Tournee::class)->createQueryBuilder('t')
            ->leftJoin('t.livraisons', 'l')
            ->addSelect('l')
            ->where('t.date > :aujourdhui')
            ->andWhere('t.livreur = :livreurId')
            ->setParameter('aujourdhui', $aujourdhui->setTime(0, 0, 0))
            ->setParameter('livreurId', $livreurId)
            ->getQuery()
            ->getResult();

        return new JsonResponse($this->normalizeTournees($tournees));
    }

    #[Route('/api/tournees/all/{livreurId}', name: 'tournees_all')]
    public function toutesLesTournees(EntityManagerInterface $entityManager, int $livreurId): JsonResponse
    {
        $tournees = $entityManager->getRepository(Tournee::class)->createQueryBuilder('t')
            ->leftJoin('t.livraisons', 'l')
            ->addSelect('l')
            ->where('t.livreur = :livreurId')
            ->setParameter('livreurId', $livreurId)
            ->orderBy('t.date', 'ASC')
            ->addOrderBy('t.creneau', 'ASC') // AM avant PM
            ->getQuery()
            ->getResult();

        return new JsonResponse($this->normalizeTournees($tournees));
    }

    #[Route('/api/tournees/{id}/livraisons', name: 'tournee_livraisons', methods: ['GET'])]
    public function getTourneeLivraisons(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $tournee = $entityManager->getRepository(Tournee::class)->find($id);

        if (!$tournee) {
            return new JsonResponse(['error' => 'Tournee not found'], 404);
        }

        return new JsonResponse($this->normalizeTournees([$tournee])[0]);
    }


    private function normalizeTournees(array $tournees): array
    {
        return array_map(function (Tournee $tournee) {
            return [
                'id' => $tournee->getId(),
                'date' => $tournee->getDate()?->format('Y-m-d'),
                'duree' => $tournee->getDuree()?->format('H:i:s'),
                'distance' => $tournee->getDistance(),
                'statut' => $tournee->getStatut(),
                'livreur_id' => $tournee->getLivreur()?->getId(),
                'creneau' => $tournee->getCreneau(),
                'livraisons' => array_map(function ($livraison) {
                    return [
                        'id' => $livraison->getId(),
                        'numero' => $livraison->getNumero(),
                        'adresse' => $livraison->getAdresse(),
                        'code_postal' => $livraison->getCodePostal(),
                        'ville' => $livraison->getVille(),
                        'client_nom' => $livraison->getClientNom(),
                        'client_prenom' => $livraison->getClientPrenom(),
                        'statut' => $livraison->getStatut(),
                        'latitude' =>$livraison->getLatitude(),
                        'longitude'=>$livraison->getLongitude()
                    ];
                }, $tournee->getLivraisons()->toArray()), 
            ];
        }, $tournees);
    }

    
    
}
