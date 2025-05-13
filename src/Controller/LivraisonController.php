<?php

namespace App\Controller;

use App\Entity\Livraison;
use App\Form\LivraisonType;
use App\Service\GeolocationService;
use App\Repository\LivraisonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/livraison')]
final class LivraisonController extends AbstractController{
    #[Route(name: 'app_livraison_index', methods: ['GET'])]
    public function index(LivraisonRepository $livraisonRepository): Response
    {
        return $this->render('livraison/index.html.twig', [
            'livraisons' => $livraisonRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_livraison_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, GeolocationService $geolocationService): Response
    {
        $livraison = new Livraison();
        $form = $this->createForm(LivraisonType::class, $livraison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $coords = $geolocationService->getCoordinates($livraison->getAdresse(). ', '. $livraison->getCodePostal() . ' ' . $livraison->getVille());
            if (!$coords || !isset($coords['latitude']) || !isset($coords['longitude'])) {
                dump("Adresse ignorée : {$livraison->getAdresse()} (Coordonnées non trouvées)");
            }
            $livraison->setLatitude($coords['latitude']);
            $livraison->setLongitude($coords['longitude']);
            $livraison->setStatut('En attente');
            $entityManager->persist($livraison);
            $entityManager->flush();

            return $this->redirectToRoute('app_livraison_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('livraison/new.html.twig', [
            'livraison' => $livraison,
            'livraisonForm' => $form,
        ]);
    }
    #[Route('/delete', name: 'app_livraison_delete_history', methods: ['GET'])]
    public function deleteHistory(Request $request, LivraisonRepository $livraisonRepository, EntityManagerInterface $entityManager): Response
    {   
            // Définir la date limite (1 an en arrière)
            $dateLimite = new \DateTime('-1 year');
    
            // Récupérer toutes les livraisons antérieures à cette date
            $oldLivraisons = $livraisonRepository->createQueryBuilder('l')
                ->where('l.date < :dateLimite')
                ->setParameter('dateLimite', $dateLimite)
                ->getQuery()
                ->getResult();
    
            // Supprimer les livraisons trouvées
            foreach ($oldLivraisons as $livraison) {
                $entityManager->remove($livraison);
            }
            
            // Exécuter la suppression
            $entityManager->flush();
    
        return $this->redirectToRoute('app_livraison_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_livraison_show', methods: ['GET'])]
    public function show(Livraison $livraison): Response
    {
        return $this->render('livraison/show.html.twig', [
            'livraison' => $livraison,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_livraison_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Livraison $livraison, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LivraisonType::class, $livraison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_livraison_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('livraison/edit.html.twig', [
            'livraison' => $livraison,
            'livraisonForm' => $form,
        ]);
    }


    

    #[Route('/{id}', name: 'app_livraison_delete', methods: ['POST'])]
    public function delete(Request $request, Livraison $livraison, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$livraison->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($livraison);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_livraison_index', [], Response::HTTP_SEE_OTHER);
    }

}
