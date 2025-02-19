<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Tournee;
use App\Entity\Trajet;
use App\Entity\Livraison;
use App\Entity\Historique;
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
            "10 Boulevard Romain Rolland, 13010 Marseille",
            "25 Rue Saint-Pierre, 13010 Marseille",
            "42 Avenue de Toulon, 13010 Marseille",
            "8 Rue de l'Obélisque, 13010 Marseille",
            "18 Rue François Mauriac, 13010 Marseille",
            "33 Boulevard Rabatau, 13010 Marseille",
            "77 Avenue de la Timone, 13010 Marseille",
            "54 Rue Louis Astruc, 13010 Marseille",
            "99 Boulevard Sakakini, 13010 Marseille",
            "12 Avenue Jean Lombard, 13010 Marseille",
        ];

        $noms = ["Dupont", "Lemoine", "Martin", "Bernard", "Dubois", "Morel", "Laurent", "Girard", "Simon", "Roche"];
        $prenoms = ["Lucas", "Emma", "Léa", "Hugo", "Noah", "Manon", "Louis", "Chloé", "Nathan", "Camille"];

        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setEmail("livreur{$i}@example.com")
                 ->setRoles(['ROLE_LIVREUR'])
                 ->setName("Livreur{$i}")
                 ->setFirstName("PrénomLivreur{$i}");

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);
            $manager->persist($user);

            for ($d = -7; $d <= 7; $d++) {
                $date = clone $today;
                $date->modify("{$d} days");

                $tourneeStatut = $d < 0 ? 'Terminée' : 'Attribuée';
                $livraisonStatut = $d < 0 ? 'Livrée' : 'Programmée';

                for ($c = 1; $c <= 2; $c++) {
                    $tournee = new Tournee();
                    $tournee->setDate($date)
                            ->setDuree(new \DateTime('02:00:00'))
                            ->setDistance(mt_rand(10, 100))
                            ->setStatut($tourneeStatut)
                            ->setLivreur($user)
                            ->setCreneau((string)$c);
                    $manager->persist($tournee);

                    foreach ($adresses as $j => $adresse) {
                        $nom = $noms[$j % count($noms)];
                        $prenom = $prenoms[$j % count($prenoms)];

                        // 🔍 Vérification que Google API retourne bien des coordonnées
                        $coords = $this->geolocationService->getCoordinates($adresse);
                        if (!$coords || !isset($coords['latitude']) || !isset($coords['longitude'])) {
                            dump("Adresse ignorée : {$adresse} (Coordonnées non trouvées)");
                            continue; 
                        }

                        $trajet = new Trajet();
                        $trajet->setDistance(mt_rand(1, 20))
                               ->setDuree(new \DateTime(sprintf('00:%02d:%02d', mt_rand(10, 59), mt_rand(0, 59))))
                               ->setOrdre($j + 1)
                               ->setTournee($tournee);
                        $manager->persist($trajet);

                        $livraison = new Livraison();
                        $livraison->setNumero("LIV-{$i}{$d}{$c}{$j}")
                                  ->setAdresse($adresse)
                                  ->setCodePostal("13010")
                                  ->setVille("Marseille")
                                  ->setClientNom($nom)
                                  ->setClientPrenom($prenom)
                                  ->setClientEmail(strtolower($prenom) . "." . strtolower($nom) . "@mail.com")
                                  ->setClientTelephone("06" . mt_rand(10000000, 99999999)) 
                                  ->setDate($date) // ✅ Ajout de la date
                                  ->setCreneau((string)$c)
                                  ->setStatut($livraisonStatut)
                                  ->setTrajet($trajet)
                                  ->setTournee($tournee)
                                  ->setLatitude($coords['latitude'])
                                  ->setLongitude($coords['longitude']);

                        $manager->persist($livraison);
                    }
                }
            }
        }

        $manager->flush();
    }
}
