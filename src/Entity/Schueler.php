<?php

namespace App\Entity;

use App\Repository\SchuelerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Trait\LdapImportable;

#[ORM\Entity(repositoryClass: SchuelerRepository::class)]
class Schueler
{
    use LdapImportable;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Kompetenzraster>
     */
    #[ORM\OneToMany(targetEntity: Kompetenzraster::class, mappedBy: 'schueler', orphanRemoval: true)]
    private Collection $kompetenzrasters;

    #[ORM\ManyToOne(inversedBy: 'schuelers')]
    private ?Klasse $klasse = null;

    public function __construct()
    {
        $this->kompetenzrasters = new ArrayCollection();
    }

    public function __toString(): string {
        return $this->getLabel();
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
            $kompetenzraster->setSchueler($this);
        }

        return $this;
    }

    public function removeKompetenzraster(Kompetenzraster $kompetenzraster): static
    {
        if ($this->kompetenzrasters->removeElement($kompetenzraster)) {
            // set the owning side to null (unless already changed)
            if ($kompetenzraster->getSchueler() === $this) {
                $kompetenzraster->setSchueler(null);
            }
        }

        return $this;
    }

    public function getKlasse(): ?Klasse
    {
        return $this->klasse;
    }

    public function setKlasse(?Klasse $klasse): static
    {
        $this->klasse = $klasse;

        return $this;
    }

}
