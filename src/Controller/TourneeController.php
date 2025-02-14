<?php 

namespace App\Controller;

use App\Entity\Tournee;
use App\Repository\TourneeRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TourneeController extends AbstractController
{
    #[Route('/api/tournee/futures/{livreurId}', name: 'get_futures_tournees', methods: ['GET'])]
    public function getFuturesTournees(int $livreurId, TourneeRepository $tourneeRepository): JsonResponse
    {
        $today = new \DateTimeImmutable('today');
        $tournees = $tourneeRepository->createQueryBuilder('t')
            ->leftJoin('t.livraisons', 'l')
            ->addSelect('l')
            ->where('t.livreur = :livreurId')
            ->andWhere('t.date > :today')
            ->setParameter('livreurId', $livreurId)
            ->setParameter('today', $today->format('Y-m-d'))
            ->orderBy('t.date', 'ASC')
            ->getQuery()
            ->getResult();
        
        return $this->formatTourneesResponse($tournees);
    }

    #[Route('/api/tournee/aujourdHui/{livreurId}', name: 'get_aujourdhui_tournees', methods: ['GET'])]
    public function getAujourdhuiTournees(int $livreurId, TourneeRepository $tourneeRepository): JsonResponse
    {
        $today = new \DateTimeImmutable('today');
        
        try {
            $tournees = $tourneeRepository->createQueryBuilder('t')
                ->leftJoin('t.livraisons', 'l')
                ->addSelect('l')
                ->where('t.livreur = :livreurId')
                ->andWhere('t.date = :today')
                ->setParameter('livreurId', $livreurId)
                ->setParameter('today', $today->format('Y-m-d'))
                ->getQuery()
                ->getResult();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur interne du serveur', 'message' => $e->getMessage()], 500);
        }
        
        return $this->formatTourneesResponse($tournees);
    }

    #[Route('/api/tournee/passees/{livreurId}', name: 'get_passees_tournees', methods: ['GET'])]
    public function getPasseesTournees(int $livreurId, TourneeRepository $tourneeRepository): JsonResponse
    {
        $today = new \DateTimeImmutable('today');
        $tournees = $tourneeRepository->createQueryBuilder('t')
            ->leftJoin('t.livraisons', 'l')
            ->addSelect('l')
            ->leftJoin('l.trajet', 'tr') // Ajout pour s'assurer que Doctrine charge bien les livraisons liées
            ->addSelect('tr')
            ->where('t.livreur = :livreurId')
            ->andWhere('t.date < :today')
            ->setParameter('livreurId', $livreurId)
            ->setParameter('today', $today->format('Y-m-d'))
            ->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult();
        
        return $this->formatTourneesResponse($tournees);
    }

    private function formatTourneesResponse(array $tournees): JsonResponse
    {
        $result = [];

        foreach ($tournees as $tournee) {
            $livraisons = [];
            foreach ($tournee->getLivraisons() as $livraison) {
                dump($livraison);
                $livraisons[] = [
                    'numero' => $livraison->getNumero(),
                    'client_prenom' => $livraison->getClientPrenom(),
                    'client_nom' => $livraison->getClientNom(),
                    'adresse' => $livraison->getAdresse(),
                    'code_postal' => $livraison->getCodePostal(),
                    'ville' => $livraison->getVille(),
                    'creneau' => $livraison->getCreneau(),
                    'statut' => $livraison->getStatut(),
                ];
            }

            $result[] = [
                'tournee_id' => $tournee->getId(),
                'date' => $tournee->getDate()->format('Y-m-d'),
                'creneau' => $tournee->getCreneau(),
                'livraisons' => $livraisons,
            ];
        }

        return new JsonResponse($result);
    }
}
