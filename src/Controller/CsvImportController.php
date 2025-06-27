<?php
namespace App\Controller;

use League\Csv\Reader;
use App\Form\CsvImportType;
use App\Form\ImportCsvType;
use App\Service\CsvService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class CsvImportController extends AbstractController
{
    #[Route('/import', name: 'csv_import')]
    public function import(Request $request, CsvService $csvService): Response
    {
        $form = $this->createForm(ImportCsvType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('csv_file')->getData();

            if ($csvFile) {
                $newFilename = uniqid() . '.' . $csvFile->guessExtension();
                try {
                    $csvFile->move($this->getParameter('csv_directory'), $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload du fichier.');
                    return $this->redirectToRoute('csv_import');
                }

                // Traitement des livraisons via le service
                $csvPath = $this->getParameter('csv_directory') . '/' . $newFilename;
                $livraisonsAjoutees = $csvService->importLivraisons(new \Symfony\Component\HttpFoundation\File\File($csvPath));

                $this->addFlash('success', "$livraisonsAjoutees livraisons importées avec succès.");

                return $this->redirectToRoute('app_livraison_index');
            }
        }

        return $this->render('csv/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

