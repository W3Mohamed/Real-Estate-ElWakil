<?php

namespace App\Entity;

use App\Repository\PromoteurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromoteurRepository::class)]
class Promoteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 15)]
    private ?string $telephone = null;

    #[ORM\ManyToOne(inversedBy: 'promoteurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Type $type = null;

    #[ORM\Column]
    private ?int $superficie_min = null;

    #[ORM\Column]
    private ?int $superficie_max = null;

    /**
     * @var Collection<int, Wilaya>
     */
    #[ORM\ManyToMany(targetEntity: Wilaya::class)]
    private Collection $wilayas;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Commune $commune = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
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

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getSuperficieMin(): ?int
    {
        return $this->superficie_min;
    }

    public function setSuperficieMin(int $superficie_min): static
    {
        $this->superficie_min = $superficie_min;

        return $this;
    }

    public function getSuperficieMax(): ?int
    {
        return $this->superficie_max;
    }

    public function setSuperficieMax(int $superficie_max): static
    {
        $this->superficie_max = $superficie_max;

        return $this;
    }

    /**
     * @return Collection<int, Wilaya>
     */
    public function getWilayas(): Collection
    {
        return $this->wilayas;
    }

    public function addWilaya(Wilaya $wilaya): static
    {
        if (!$this->wilayas->contains($wilaya)) {
            $this->wilayas->add($wilaya);
        }

        return $this;
    }

    public function removeWilaya(Wilaya $wilaya): static
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
