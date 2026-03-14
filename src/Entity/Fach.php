<?php

namespace App\Entity;

use App\Repository\FachRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Trait\LdapImportable;

#[ORM\Entity(repositoryClass: FachRepository::class)]
class Fach
{
    use LdapImportable;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Kompetenz>
     */
    #[ORM\OneToMany(targetEntity: Kompetenz::class, mappedBy: 'fach')]
    private Collection $kompetenzen;

    public function __construct()
    {
        $this->kompetenzen = new ArrayCollection();
    }

    public function __toString(): string {
        return $this->getLabel();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Kompetenz>
     */
    public function getKompetenzen(): Collection
    {
        return $this->kompetenzen;
    }

    public function addKompetenz(Kompetenz $kompetenz): static
    {
        if (!$this->kompetenzen->contains($kompetenz)) {
            $this->kompetenzen->add($kompetenz);
            $kompetenz->setFach($this);
        }

        return $this;
    }

    public function removeKompetenz(Kompetenz $kompetenz): static
    {
        if ($this->kompetenzen->removeElement($kompetenz)) {
            // set the owning side to null (unless already changed)
            if ($kompetenz->getFach() === $this) {
                $kompetenz->setFach(null);
            }
        }

        return $this;
    }

    
}
