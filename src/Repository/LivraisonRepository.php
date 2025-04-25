<?php

namespace App\Repository;

use App\Entity\Livraison;
use App\Entity\Historique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LivraisonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livraison::class);
    }

    public function saveWithHistory(Livraison $livraison, string $evenement): void
    {
        $entityManager = $this->getEntityManager();

        $historique = new Historique();
        $historique->setLivraison($livraison);
        $historique->setDateEnregistrement(new \DateTime());
        $historique->setEvenement($evenement);
        $historique->setNumero($livraison->getNumero());
        $historique->setAdresse($livraison->getAdresse());
        $historique->setCodePostal($livraison->getCodePostal());
        $historique->setVille($livraison->getVille());
        $historique->setClientNom($livraison->getClientNom());
        $historique->setClientPrenom($livraison->getClientPrenom());
        $historique->setClientEmail($livraison->getClientEmail());
        $historique->setClientTelephone($livraison->getClientTelephone());
        $historique->setDate($livraison->getDate());
        $historique->setCreneau($livraison->getCreneau());
        $historique->setStatut($livraison->getStatut());

        $entityManager->persist($livraison);
        $entityManager->persist($historique);
        $entityManager->flush();
    }
}
