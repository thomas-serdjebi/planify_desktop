<?php

namespace App\Controller;

use App\Entity\Livraison;
use App\Repository\LivraisonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LivraisonController extends AbstractController
{
    #[Route('/livraison/{id}/modifier', name: 'modifier_livraison', methods: ['POST'])]
    public function modifierLivraison(
        Livraison $livraison,
        Request $request,
        LivraisonRepository $livraisonRepository
    ): Response {
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['statut'])) {
            $livraison->setStatut($data['statut']);
        }
        if (isset($data['adresse'])) {
            $livraison->setAdresse($data['adresse']);
        }

        $livraisonRepository->saveWithHistory($livraison, "Mise à jour");

        return $this->json([
            'message' => 'Livraison mise à jour avec succès.',
            'livraison' => $livraison
        ]);
    }

    #[Route('/livraison/{id}/statut', name: 'update_livraison_statut', methods: ['PATCH'])]
    public function updateStatut(
        Livraison $livraison,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['statut'])) {
            return $this->json(['error' => 'Le statut est requis'], Response::HTTP_BAD_REQUEST);
        }

        // Mise à jour du statut de la livraison
        $livraison->setStatut($data['statut']);
        $entityManager->persist($livraison);
        $entityManager->flush();

        return $this->json([
            'message' => 'Statut de la livraison mis à jour avec succès.',
            'livraison' => [
                'id' => $livraison->getId(),
                'statut' => $livraison->getStatut(),
            ]
        ], Response::HTTP_OK);
    }
}
