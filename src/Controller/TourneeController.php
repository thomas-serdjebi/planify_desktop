<?php

namespace App\Controller;

use App\Entity\Tournee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TourneeController extends AbstractController
{

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
