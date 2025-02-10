<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Tournee;
use App\Entity\Trajet;
use App\Entity\Livraison;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $dateToday = new \DateTime(); // Date du jour

        for ($i = 1; $i <= 5; $i++) {
            // Création de l'utilisateur
            $user = new User();
            $user->setEmail("livreur{$i}@example.com")
                 ->setRoles(['ROLE_LIVREUR'])
                 ->setName("Nom{$i}")
                 ->setFirstName("Prénom{$i}");

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);
            $manager->persist($user);

            // Création des 2 tournées (créneau 1 et 2)
            for ($c = 1; $c <= 2; $c++) {
                $tournee = new Tournee();
                $tournee->setDate($dateToday)
                        ->setDuree(new \DateTime('02:00:00')) // Exemple de durée : 2 heures
                        ->setDistance(mt_rand(10, 100)) // Distance aléatoire
                        ->setStatut('En cours')
                        ->setLivreur($user)
                        ->setCreneau("{$c}");
                $manager->persist($tournee);

                // Création de 10 trajets avec livraisons
                for ($j = 1; $j <= 10; $j++) {
                    $trajet = new Trajet();
                    $trajet->setDistance(mt_rand(1, 20))
                           ->setDuree(new \DateTime(sprintf('00:%02d:%02d', mt_rand(10, 59), mt_rand(0, 59))))
                           ->setOrdre($j)
                           ->setTournee($tournee);
                    $manager->persist($trajet);

                    $livraison = new Livraison();
                    $livraison->setNumero("LIV-{$i}{$c}{$j}")
                              ->setAdresse("10 Rue Exemple, Ville {$j}")
                              ->setCodePostal("7500{$j}")
                              ->setVille("Paris")
                              ->setClientNom("ClientNom{$j}")
                              ->setClientPrenom("ClientPrenom{$j}")
                              ->setClientEmail("client{$j}@mail.com")
                              ->setClientTelephone("06XXXXXXXX")
                              ->setDate($dateToday)
                              ->setCreneau($c)
                              ->setStatut('En attente')
                              ->setTrajet($trajet);
                    $manager->persist($livraison);
                }
            }
        }

        $manager->flush();
    }
}
