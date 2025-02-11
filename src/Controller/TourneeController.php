<?php 

namespace App\Controller;

use App\Entity\Tournee;
use App\Repository\TourneeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TourneeController extends AbstractController
{
    #[Route('/api/tournee/{livreurId}/{date}/jour', name: 'get_tournee_du_jour', methods: ['GET'])]
    public function getTourneeDuJour(int $livreurId, string $date, TourneeRepository $tourneeRepository): JsonResponse
    {
        // Convertir la date reçue dans l'URL en un objet \DateTime
        $dateDuJour = \DateTime::createFromFormat('Y-m-d', $date);
    
        // Vérifier si la conversion a échoué
        if (!$dateDuJour) {
            return new JsonResponse(['message' => 'Date invalide. Le format attendu est Y-m-d.'], 400);
        }
    
        // Récupérer la tournée du livreur pour la date spécifiée
        $tournee = $tourneeRepository->findOneBy([
            'livreur' => $livreurId,
            'date' => $dateDuJour
        ]);
    
        // Gérer l'absence de tournée
        if (!$tournee) {
            return new JsonResponse(['message' => 'Aucune tournée trouvée pour ce livreur à cette date.'], 404);
        }
    
        // Récupérer les livraisons de la tournée, triées par ordre de passage
        $livraisons = [];
        foreach ($tournee->getTrajets() as $trajet) {
            $livraison = $trajet->getLivraison();
            if ($livraison) {
                $livraisons[] = [
                    'numero' => $livraison->getNumero(),
                    'client_prenom' => $livraison->getClientPrenom(),
                    'client_nom' => $livraison->getClientNom(),
                    'adresse' => $livraison->getAdresse(),
                    'code_postal' => $livraison->getCodePostal(),
                    'ville' => $livraison->getVille(),
                    'creneau' => $livraison->getCreneau(),
                ];
            }
        }
    
        // Retourner les données au format JSON
        return new JsonResponse([
            'tournee_id' => $tournee->getId(),
            'date' => $tournee->getDate()->format('Y-m-d'),
            'livraisons' => $livraisons
        ]);
    }
    
}
