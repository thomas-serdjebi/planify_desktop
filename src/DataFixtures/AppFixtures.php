<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Livraison;
use App\Service\GeolocationService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private GeolocationService $geolocationService;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        GeolocationService $geolocationService
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->geolocationService = $geolocationService;
    }

    public function load(ObjectManager $manager): void
    {
        $dateToday = new \DateTime();

        $adresses = [
            "10 Boulevard Baille, 13010 Marseille, France",
            "25 Rue de la Loubière, 13010 Marseille, France",
            "8 Avenue Foch, 13010 Marseille, France",
            "15 Rue Saint-Pierre, 13010 Marseille, France",
            "42 Rue Jean Martin, 13010 Marseille, France",
            "7 Rue de l'Espérance, 13010 Marseille, France",
            "33 Avenue des Olives, 13010 Marseille, France",
            "18 Rue de la République, 13010 Marseille, France",
            "5 Rue des Trois Rois, 13010 Marseille, France",
            "12 Rue du Vallon, 13010 Marseille, France",
            "15 Boulevard Romain Rolland, 13009 Marseille, France",
            "8 Chemin de Morgiou, 13009 Marseille, France",
            "22 Avenue de Luminy, 13009 Marseille, France",
            "5 Rue Mignard, 13009 Marseille, France",
            "30 Boulevard du Redon, 13009 Marseille, France",
            "42 Avenue du Prado, 13008 Marseille, France",
            "15 Rue Paradis, 13008 Marseille, France",
            "8 Rue Breteuil, 13008 Marseille, France",
            "33 Rue Saint-Ferréol, 13008 Marseille, France",
            "7 Rue de Rome, 13008 Marseille, France"
        ];

        $clients = [
            ["Jean", "Dupont"],
            ["Marie", "Lemoine"],
            ["Sophie", "Durand"],
            ["Paul", "Bernard"],
            ["Nicolas", "Morel"],
            ["Camille", "Petit"],
            ["Lucie", "Robert"],
            ["Thomas", "Richard"],
            ["Julie", "Dubois"],
            ["François", "Leroy"]
        ];

        // Création de 5 livreurs
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setEmail("livreur{$i}@example.com")
                ->setRoles(['ROLE_LIVREUR'])
                ->setName("Nom{$i}")
                ->setFirstName("Prénom{$i}");

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);
            $manager->persist($user);
        }

        // Création des livraisons pour aujourd'hui + 7 jours
        for ($j = 0; $j <= 7; $j++) {
            $date = (clone $dateToday)->modify("+{$j} days");

            for ($c = 1; $c <= 2; $c++) { // 2 créneaux par jour
                for ($k = 0; $k < 20; $k++) {
                    $adresseComplet = $adresses[$k % count($adresses)];
                    $coords = $this->geolocationService->getCoordinates($adresseComplet);

                    [$prenom, $nom] = $clients[array_rand($clients)];

                    $livraison = new Livraison();
                    $livraison->setNumero("LIV-{$j}{$c}{$k}")
                        ->setAdresse($adresseComplet)
                        ->setVille("Marseille")
                        ->setCodePostal(intval(substr($adresseComplet, -17, 5)))
                        ->setClientNom($nom)
                        ->setClientPrenom($prenom)
                        ->setClientEmail(strtolower($prenom) . "." . strtolower($nom) . "@mail.com")
                        ->setClientTelephone("06" . mt_rand(10000000, 99999999))
                        ->setDate($date)
                        ->setCreneau($c)
                        ->setStatut('En attente');

                    if ($coords) {
                        $livraison->setLatitude($coords['latitude']);
                        $livraison->setLongitude($coords['longitude']);
                    }

                    $manager->persist($livraison);
                }
            }
        }

        $manager->flush();
    }
}
