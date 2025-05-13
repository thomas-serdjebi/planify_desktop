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

    public function __construct(UserPasswordHasherInterface $passwordHasher, GeolocationService $geolocationService)
    {
        $this->passwordHasher = $passwordHasher;
        $this->geolocationService = $geolocationService;
    }

    public function load(ObjectManager $manager): void
    {
        $today = new \DateTime();
        $adresses = [
            "10 Boulevard Romain Rolland", "25 Rue Saint-Pierre", "42 Avenue de Toulon", "14 Rue Martiny",
            "18 Rue François Mauriac", "33 Boulevard Rabatau", "77 Avenue de la Timone", "54 Rue Louis Astruc",
            "99 Boulevard Sakakini", "12 Avenue Jean Lombard", "3 Rue de Lodi", "15 Avenue du Prado", 
            "82 Boulevard Baille", "23 Rue des Bons Enfants", "50 Rue Saint-Ferréol", "61 Rue Paradis",
            "19 Rue Montgrand", "90 Boulevard Rabatau", "28 Rue de Rome", "64 Avenue de Mazargues",
            "35 Rue Sainte", "2 Rue Dragon", "76 Boulevard Chave", "11 Avenue de la Corse",
            "27 Boulevard Sakakini", "14 Rue Breteuil", "5 Rue de la Palud", "32 Rue Consolat",
            "19 Boulevard Tellène", "88 Avenue des Chartreux"
        ];

        $noms = ["Dupont", "Lemoine", "Martin", "Bernard", "Dubois", "Morel", "Laurent", "Girard", "Simon", "Roche"];
        $prenoms = ["Lucas", "Emma", "Léa", "Hugo", "Noah", "Manon", "Louis", "Chloé", "Nathan", "Camille"];

        for ($i = 1; $i <= 5; $i++) {
            $livreur = new User();
            $livreur->setEmail("livreur{$i}@example.com")
                    ->setRoles(['ROLE_LIVREUR'])
                    ->setName("Livreur {$i}")
                    ->setFirstName("Prenom{$i}");
        
            $hashedPassword = $this->passwordHasher->hashPassword($livreur, 'password123');
            $livreur->setPassword($hashedPassword);
            $manager->persist($livreur);
        }

        for ($d = 0; $d <= 7; $d++) {
            $date = (clone $today)->modify("+$d days");

            foreach ([1, 2] as $creneau) {
                for ($i = 0; $i < 15; $i++) {
                    $index = ($i + $creneau * 15 + $d * 30) % count($adresses);
                    $adresse = $adresses[$index];
                    $fullAdresse = "{$adresse}, Marseille";

                    // Obtenir coordonnées + code postal depuis le service
                    $coords = $this->geolocationService->getCoordinates($fullAdresse);
                    if (
                        !$coords || 
                        !isset($coords['latitude'], $coords['longitude'], $coords['postal_code'])
                    ) {
                        dump("Adresse ignorée : {$fullAdresse} (Coordonnées ou code postal non trouvés)");
                        continue;
                    }

                    $nom = $noms[$index % count($noms)];
                    $prenom = $prenoms[$index % count($prenoms)];

                    $livraison = new Livraison();
                    $livraison->setNumero("LIV-{$d}{$creneau}{$i}")
                              ->setAdresse($fullAdresse)
                              ->setCodePostal($coords['postal_code'])
                              ->setVille("Marseille")
                              ->setClientNom($nom)
                              ->setClientPrenom($prenom)
                              ->setClientEmail(strtolower($prenom) . "." . strtolower($nom) . "@mail.com")
                              ->setClientTelephone("06" . mt_rand(10000000, 99999999))
                              ->setDate($date)
                              ->setCreneau((string)$creneau)
                              ->setStatut("En attente")
                              ->setLatitude($coords['latitude'])
                              ->setLongitude($coords['longitude']);

                    $manager->persist($livraison);
                }
            }
        }

        $manager->flush();
    }
}
