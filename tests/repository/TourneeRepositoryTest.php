<?php

namespace App\Tests\Repository;

use App\Entity\Tournee;
use App\Entity\Livraison;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TourneeRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testFindTourneeWithLivraisons(): void
    {
        $livreurId = 1; // Change selon tes données
        $today = new \DateTimeImmutable('today');

        $query = $this->entityManager->createQuery("
            SELECT t, l 
            FROM App\Entity\Tournee t 
            LEFT JOIN t.livraisons l
            WHERE t.livreur = :livreurId
            AND t.date = :today
        ");
        $query->setParameter('livreurId', $livreurId);
        $query->setParameter('today', $today->format('Y-m-d'));

        $tournees = $query->getResult();

        $this->assertNotEmpty($tournees, "Aucune tournée trouvée !");
        
        foreach ($tournees as $tournee) {
            $this->assertNotEmpty($tournee->getLivraisons(), "Les livraisons ne sont pas récupérées !");
        }
    }
}
