<?php

namespace App\Entity;

use App\Repository\ClientsRepository;
use App\Service\BienMatchingService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientsRepository::class)]
class Clients
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 15)]
    private ?string $telephone = null;

    #[ORM\ManyToMany(targetEntity: Type::class, inversedBy: 'clients')]
    private Collection $type;

    #[ORM\Column]
    private ?int $budjetMin = null;

    /**
     * @var Collection<int, Bien>
     */
    #[ORM\ManyToMany(targetEntity: Bien::class, inversedBy: 'clients')]
    private Collection $biens;

    #[ORM\ManyToMany(targetEntity: Wilaya::class, inversedBy: 'clients')]
    private Collection $wilayas;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Commune $commune = null;

    #[ORM\Column]
    private ?int $budjetMax = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

// + les getters/setters

    public function __construct()
    {
        $this->biens = new ArrayCollection();
        $this->wilayas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getType(): Collection
    {
        return $this->type;
    }

    public function setType(Collection $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getBudjetMin(): ?int
    {
        return $this->budjetMin;
    }

    public function setBudjetMin(int $budjetMin): static
    {
        $this->budjetMin = $budjetMin;

        return $this;
    }

    /**
     * @return Collection<int, Bien>
     */
    public function getBiens(): Collection
    {
        return $this->biens;
    }

    public function addBien(Bien $bien): static
    {
        if (!$this->biens->contains($bien)) {
            $this->biens->add($bien);
        }

        return $this;
    }

    public function removeBien(Bien $bien): static
    {
        $this->biens->removeElement($bien);

        return $this;
    }

    public function getWilayas(): Collection
    {
        return $this->wilayas;
    }

    public function addWilaya(Wilaya $wilaya): self
    {
        if (!$this->wilayas->contains($wilaya)) {
            $this->wilayas[] = $wilaya;
        }
        return $this;
    }

    public function removeWilaya(Wilaya $wilaya): self
    {
        $this->wilayas->removeElement($wilaya);
        return $this;
    }

    public function getCommune(): ?Commune
    {
        return $this->commune;
    }

    public function setCommune(?Commune $commune): static
    {
        $this->commune = $commune;

        return $this;
    }

    public function getBudjetMax(): ?int
    {
        return $this->budjetMax;
    }

    public function setBudjetMax(int $budjetMax): static
    {
        $this->budjetMax = $budjetMax;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
