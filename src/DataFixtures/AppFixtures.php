<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Tournee;
use App\Entity\Trajet;
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
        $adresseMarseille10 = [
            "10 Boulevard Baille, Marseille",
            "5 Rue Jean Martin, Marseille",
            "15 Rue Saint-Pierre, Marseille",
            "8 Avenue Foch, Marseille",
            "22 Rue de la Loubière, Marseille"
        ];

        $clients = [
            ["Jean", "Dupont"],
            ["Marie", "Lemoine"],
            ["Sophie", "Durand"],
            ["Paul", "Bernard"],
            ["Nicolas", "Morel"]
        ];

        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setEmail("livreur{$i}@example.com")
                ->setRoles(['ROLE_LIVREUR'])
                ->setName("Nom{$i}")
                ->setFirstName("Prénom{$i}");

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);
            $manager->persist($user);

            // Tournées passées (statut Livrée)
            for ($j = 1; $j <= 7; $j++) {
                $pastDate = (clone $dateToday)->modify("-{$j} days");

                for ($c = 1; $c <= 2; $c++) {
                    $tournee = new Tournee();
                    $tournee->setDate($pastDate)
                        ->setDuree(7200) // 2h en secondes
                        ->setDistance(mt_rand(10, 100))
                        ->setStatut('Terminée')
                        ->setLivreur($user)
                        ->setCreneau("{$c}");

                    $manager->persist($tournee);

                    for ($k = 0; $k < 5; $k++) {
                        $trajet = new Trajet();
                        $trajet->setDistance(mt_rand(1, 20))
                            ->setDuree(mt_rand(600, 1800)) // 10 à 30 min en secondes
                            ->setOrdre($k + 1)
                            ->setTournee($tournee);
                        $manager->persist($trajet);

                        [$prenom, $nom] = $clients[array_rand($clients)];
                        $adresse = $adresseMarseille10[array_rand($adresseMarseille10)];
                        $fullAddress = $adresse . ', 13010 Marseille, France';

                        $coords = $this->geolocationService->getCoordinates($fullAddress);

                        $livraison = new Livraison();
                        $livraison->setNumero("LIV-{$i}{$c}{$j}{$k}")
                            ->setAdresse($adresse)
                            ->setCodePostal("13010")
                            ->setVille("Marseille")
                            ->setClientNom($nom)
                            ->setClientPrenom($prenom)
                            ->setClientEmail(strtolower($prenom) . "." . strtolower($nom) . "@mail.com")
                            ->setClientTelephone("06XXXXXXXX")
                            ->setDate($pastDate)
                            ->setCreneau($c)
                            ->setStatut('Livrée')
                            ->setTournee($tournee)
                            ->setTrajet($trajet);

                        if ($coords) {
                            $livraison->setLatitude($coords['latitude']);
                            $livraison->setLongitude($coords['longitude']);
                        }

                        $tournee->addLivraison($livraison);
                        $manager->persist($livraison);
                    }
                }
            }

            // Tournées aujourd’hui et futures
            for ($j = 0; $j <= 7; $j++) {
                $futureDate = (clone $dateToday)->modify("+{$j} days");

                for ($c = 1; $c <= 2; $c++) {
                    $tournee = new Tournee();
                    $tournee->setDate($futureDate)
                        ->setDuree(7200)
                        ->setDistance(mt_rand(10, 100))
                        ->setStatut('Attribuée')
                        ->setLivreur($user)
                        ->setCreneau("{$c}");
                    $manager->persist($tournee);
                }
            }
        }

        $manager->flush();
    }
}
