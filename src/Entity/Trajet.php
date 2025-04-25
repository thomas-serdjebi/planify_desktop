<?php

namespace App\Entity;

use App\Repository\TrajetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
#[ORM\Entity(repositoryClass: TrajetRepository::class)]
class Trajet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $distance = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $duree = null;

    #[ORM\Column(nullable: true)]
    private ?int $ordre = null;

    #[ORM\ManyToOne(inversedBy: 'trajets')]
    private ?Tournee $tournee = null;

    #[ORM\OneToOne(mappedBy: 'trajet', cascade: ['persist', 'remove'])]
    private ?Livraison $livraison = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDistance(): ?float
    {
        return $this->distance;
    }

    public function setDistance(float $distance): static
    {
        $this->distance = $distance;

        return $this;
    }

    public function getDuree(): ?\DateTimeInterface
    {
        return $this->duree;
    }

    public function setDuree(\DateTimeInterface $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getTournee(): ?Tournee
    {
        return $this->tournee;
    }

    public function setTournee(?Tournee $tournee): static
    {
        $this->tournee = $tournee;

        return $this;
    }

    public function getLivraison(): ?Livraison
    {
        return $this->livraison;
    }

    public function setLivraison(?Livraison $livraison): static
    {
        // unset the owning side of the relation if necessary
        if ($livraison === null && $this->livraison !== null) {
            $this->livraison->setTrajet(null);
        }

        // set the owning side of the relation if necessary
        if ($livraison !== null && $livraison->getTrajet() !== $this) {
            $livraison->setTrajet($this);
        }

        $this->livraison = $livraison;

        return $this;
    }
}
