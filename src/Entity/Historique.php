<?php

namespace App\Entity;

use App\Repository\HistoriqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
#[ORM\Entity(repositoryClass: HistoriqueRepository::class)]
class Historique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'historiques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Livraison $livraison = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $idLivreurPrv = null; // Ancien livreur

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $idLivreurNew = null; // Nouveau livreur

    #[ORM\Column(length: 255)]
    private ?string $contenu = null;

    #[ORM\Column(length: 255)]
    private ?string $evenement = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $statutLivraisonPrv = null; // Ancien statut de livraison

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $statutLivraisonNew = null; // Nouveau statut de livraison

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLivraison(): ?Livraison
    {
        return $this->livraison;
    }

    public function setLivraison(?Livraison $livraison): static
    {
        $this->livraison = $livraison;
        return $this;
    }

    public function getIdLivreurPrv(): ?User
    {
        return $this->idLivreurPrv;
    }

    public function setIdLivreurPrv(?User $idLivreurPrv): static
    {
        $this->idLivreurPrv = $idLivreurPrv;
        return $this;
    }

    public function getIdLivreurNew(): ?User
    {
        return $this->idLivreurNew;
    }

    public function setIdLivreurNew(?User $idLivreurNew): static
    {
        $this->idLivreurNew = $idLivreurNew;
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getEvenement(): ?string
    {
        return $this->evenement;
    }

    public function setEvenement(string $evenement): static
    {
        $this->evenement = $evenement;
        return $this;
    }

    public function getStatutLivraisonPrv(): ?string
    {
        return $this->statutLivraisonPrv;
    }

    public function setStatutLivraisonPrv(?string $statutLivraisonPrv): static
    {
        $this->statutLivraisonPrv = $statutLivraisonPrv;
        return $this;
    }

    public function getStatutLivraisonNew(): ?string
    {
        return $this->statutLivraisonNew;
    }

    public function setStatutLivraisonNew(?string $statutLivraisonNew): static
    {
        $this->statutLivraisonNew = $statutLivraisonNew;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }
}
