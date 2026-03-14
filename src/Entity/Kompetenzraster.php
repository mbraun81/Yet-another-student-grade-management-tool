<?php

namespace App\Entity;

use App\Repository\KompetenzrasterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KompetenzrasterRepository::class)]
class Kompetenzraster
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'kompetenzrasters')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Schueler $schueler = null;

    #[ORM\ManyToOne(inversedBy: 'kompetenzrasters')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Kompetenz $kompetenz = null;

    #[ORM\Column(nullable: true)]
    private ?int $a = null;

    #[ORM\Column(nullable: true)]
    private ?int $b = null;

    #[ORM\Column(nullable: true)]
    private ?int $c = null;

    #[ORM\Column(nullable: true)]
    private ?int $d = null;

    #[ORM\Column(nullable: true)]
    private ?int $e = null;

    #[ORM\Column(nullable: true)]
    private ?int $f = null;

    #[ORM\Column(nullable: true)]
    private ?int $g = null;

    #[ORM\Column(nullable: true)]
    private ?int $h = null;

    #[ORM\Column(nullable: true)]
    private ?int $i = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSchueler(): ?Schueler
    {
        return $this->schueler;
    }

    public function setSchueler(?Schueler $schueler): static
    {
        $this->schueler = $schueler;

        return $this;
    }

    public function getKompetenz(): ?Kompetenz
    {
        return $this->kompetenz;
    }

    public function setKompetenz(?Kompetenz $kompetenz): static
    {
        $this->kompetenz = $kompetenz;

        return $this;
    }

    public function getA(): ?int
    {
        return $this->a;
    }

    public function setA(?int $a): static
    {
        $this->a = $a;

        return $this;
    }

    public function getB(): ?int
    {
        return $this->b;
    }

    public function setB(?int $b): static
    {
        $this->b = $b;

        return $this;
    }

    public function getC(): ?int
    {
        return $this->c;
    }

    public function setC(?int $c): static
    {
        $this->c = $c;

        return $this;
    }

    public function getD(): ?int
    {
        return $this->d;
    }

    public function setD(?int $d): static
    {
        $this->d = $d;

        return $this;
    }

    public function getE(): ?int
    {
        return $this->e;
    }

    public function setE(?int $e): static
    {
        $this->e = $e;

        return $this;
    }

    public function getF(): ?int
    {
        return $this->f;
    }

    public function setF(?int $f): static
    {
        $this->f = $f;

        return $this;
    }

    public function getG(): ?int
    {
        return $this->g;
    }

    public function setG(?int $g): static
    {
        $this->g = $g;

        return $this;
    }

    public function getH(): ?int
    {
        return $this->h;
    }

    public function setH(?int $h): static
    {
        $this->h = $h;

        return $this;
    }

    public function getI(): ?int
    {
        return $this->i;
    }

    public function setI(?int $i): static
    {
        $this->i = $i;

        return $this;
    }
    
    public function getAvg(): int
    {
        $ranges = [
            [$this->a, 0, 10],
            [$this->b, 11, 20],
            [$this->c, 21, 30],
            [$this->d, 31, 40],
            [$this->e, 41, 50],
            [$this->f, 51, 60],
            [$this->g, 61, 70],
            [$this->h, 71, 80],
            [$this->i, 81, 90],
        ];
        
        $sum = 0;
        $count = 0;
        
        foreach ($ranges as [$value, $min, $max]) {
            $value = (int)$value;
            
            if ($value >= $min && $value <= $max) {
                $sum += $value;
                $count++;
            }
        }
        
        return $count > 0 ? intdiv($sum, $count) : 0;
    }
}
