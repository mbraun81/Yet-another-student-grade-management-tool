<?php

namespace App\Entity;

use App\Repository\KlasseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Trait\LdapImportable;

#[ORM\Entity(repositoryClass: KlasseRepository::class)]
class Klasse
{
    use LdapImportable;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'klasses')]
    private ?Lehrer $klassenlehrer = null;

    /**
     * @var Collection<int, Lehrer>
     */
    #[ORM\ManyToMany(targetEntity: Lehrer::class, inversedBy: 'fachklassen')]
    private Collection $fachlehrer;

    /**
     * @var Collection<int, Schueler>
     */
    #[ORM\OneToMany(targetEntity: Schueler::class, mappedBy: 'klasse')]
    private Collection $schuelers;

    public function __construct()
    {
        $this->fachlehrer = new ArrayCollection();
        $this->schuelers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKlassenlehrer(): ?Lehrer
    {
        return $this->klassenlehrer;
    }

    public function setKlassenlehrer(?Lehrer $klassenlehrer): static
    {
        $this->klassenlehrer = $klassenlehrer;

        return $this;
    }

    /**
     * @return Collection<int, Lehrer>
     */
    public function getFachlehrer(): Collection
    {
        return $this->fachlehrer;
    }

    public function addFachlehrer(Lehrer $fachlehrer): static
    {
        if (!$this->fachlehrer->contains($fachlehrer)) {
            $this->fachlehrer->add($fachlehrer);
        }

        return $this;
    }

    public function removeFachlehrer(Lehrer $fachlehrer): static
    {
        $this->fachlehrer->removeElement($fachlehrer);

        return $this;
    }

    /**
     * @return Collection<int, Schueler>
     */
    public function getSchuelers(): Collection
    {
        return $this->schuelers;
    }

    public function addSchueler(Schueler $schueler): static
    {
        if (!$this->schuelers->contains($schueler)) {
            $this->schuelers->add($schueler);
            $schueler->setKlasse($this);
        }

        return $this;
    }

    public function removeSchueler(Schueler $schueler): static
    {
        if ($this->schuelers->removeElement($schueler)) {
            // set the owning side to null (unless already changed)
            if ($schueler->getKlasse() === $this) {
                $schueler->setKlasse(null);
            }
        }

        return $this;
    }
}
