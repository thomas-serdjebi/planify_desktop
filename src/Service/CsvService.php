<?php
namespace App\Service;

use App\Entity\Livraison;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\File\File;

class CsvService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function importLivraisons(File $csvFile): int
    {
        $csv = Reader::createFromPath($csvFile->getPathname(), 'r');
        $csv->setHeaderOffset(0); // Définit la première ligne comme en-tête

        $livraisonsAjoutees = 0;
        foreach ($csv as $record) {
            $livraison = new Livraison();
            $livraison->setNumero($record['numero'] ?? null);
            $livraison->setDate(new \DateTime($record['date'] ?? 'now'));
            $livraison->setCodePostal($record['code_postal'] ?? null);
            $livraison->setVille($record['ville'] ?? null);
            $livraison->setClientEmail($record['client_email'] ?? null);
            $livraison->setClientNom($record['client_nom'] ?? null);
            $livraison->setClientPrenom($record['client_prenom'] ?? null);
            $livraison->setAdresse($record['telephone'] ?? null);
            $livraison->setLongitude($record['longitude'] ?? null);
            $livraison->setLatitude($record['latitude'] ?? null);


            
            $this->entityManager->persist($livraison);
            $livraisonsAjoutees++;
        }

        $this->entityManager->flush();

        return $livraisonsAjoutees;
    }
}
