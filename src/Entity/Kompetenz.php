<?php

namespace App\Entity;

use App\Repository\KompetenzRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KompetenzRepository::class)]
#[ORM\UniqueConstraint(fields: ['name'])]
class Kompetenz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Kompetenzraster>
     */
    #[ORM\OneToMany(targetEntity: Kompetenzraster::class, mappedBy: 'kompetenz', orphanRemoval: true)]
    private Collection $kompetenzrasters;

    #[ORM\ManyToOne(inversedBy: 'kompetenzs')]
    private ?Fach $fach = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    public function __construct()
    {
        $this->kompetenzrasters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Kompetenzraster>
     */
    public function getKompetenzrasters(): Collection
    {
        return $this->kompetenzrasters;
    }

    public function addKompetenzraster(Kompetenzraster $kompetenzraster): static
    {
        if (!$this->kompetenzrasters->contains($kompetenzraster)) {
            $this->kompetenzrasters->add($kompetenzraster);
            $kompetenzraster->setKompetenz($this);
        }

        return $this;
    }

    public function removeKompetenzraster(Kompetenzraster $kompetenzraster): static
    {
        if ($this->kompetenzrasters->removeElement($kompetenzraster)) {
            // set the owning side to null (unless already changed)
            if ($kompetenzraster->getKompetenz() === $this) {
                $kompetenzraster->setKompetenz(null);
            }
        }

        return $this;
    }

    public function getFach(): ?Fach
    {
        return $this->fach;
    }

    public function setFach(?Fach $fach): static
    {
        $this->fach = $fach;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
