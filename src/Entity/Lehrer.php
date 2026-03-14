<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LehrerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Trait\LdapImportable;

#[ORM\Entity(repositoryClass: LehrerRepository::class)]
#[ORM\Table(name: 'lehrer')]
class Lehrer implements UserInterface, EquatableInterface, TwoFactorInterface
{
    use LdapImportable;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $ldapUsername;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $totpSecret = null;

    #[ORM\Column]
    private bool $totpEnabled = false;

    #[ORM\Column(type: 'json')]
    private array $roles = ['ROLE_TEACHER'];

    /**
     * @var Collection<int, Klasse>
     */
    #[ORM\OneToMany(targetEntity: Klasse::class, mappedBy: 'klassenlehrer')]
    private Collection $klasses;

    /**
     * @var Collection<int, Klasse>
     */
    #[ORM\ManyToMany(targetEntity: Klasse::class, mappedBy: 'fachlehrer')]
    private Collection $fachklassen;

    public function __construct()
    {
        $this->klasses = new ArrayCollection();
        $this->fachklassen = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getUserIdentifier();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLdapUsername(): string
    {
        return $this->ldapUsername;
    }

    public function setLdapUsername(string $ldapUsername): static
    {
        $this->ldapUsername = $ldapUsername;

        return $this;
    }

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(?string $totpSecret): static
    {
        $this->totpSecret = $totpSecret;

        return $this;
    }

    public function isTotpEnabled(): bool
    {
        return $this->totpEnabled;
    }

    public function setTotpEnabled(bool $totpEnabled): static
    {
        $this->totpEnabled = $totpEnabled;

        return $this;
    }

    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    // UserInterface

    public function getUserIdentifier(): string
    {
        return $this->ldapUsername;
    }

    public function eraseCredentials(): void
    {
        // No local credentials to erase
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        return $this->ldapUsername === $user->ldapUsername;
    }

    // TwoFactorInterface (TOTP)

    public function isTotpAuthenticationEnabled(): bool
    {
        return $this->totpEnabled && $this->totpSecret !== null;
    }

    public function getTotpAuthenticationUsername(): string|null
    {
        return $this->ldapUsername;
    }

    public function getTotpAuthenticationConfiguration(): TotpConfigurationInterface|null
    {
        if ($this->totpSecret === null) {
            return null;
        }

        return new TotpConfiguration(
            $this->totpSecret,
            TotpConfiguration::ALGORITHM_SHA1,
            30,
            6,
        );
    }

    /**
     * @return Collection<int, Klasse>
     */
    public function getKlasses(): Collection
    {
        return $this->klasses;
    }

    public function addKlass(Klasse $klass): static
    {
        if (!$this->klasses->contains($klass)) {
            $this->klasses->add($klass);
            $klass->setKlassenlehrer($this);
        }

        return $this;
    }

    public function removeKlass(Klasse $klass): static
    {
        if ($this->klasses->removeElement($klass)) {
            // set the owning side to null (unless already changed)
            if ($klass->getKlassenlehrer() === $this) {
                $klass->setKlassenlehrer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Klasse>
     */
    public function getFachklassen(): Collection
    {
        return $this->fachklassen;
    }

    public function addFachklassen(Klasse $fachklassen): static
    {
        if (!$this->fachklassen->contains($fachklassen)) {
            $this->fachklassen->add($fachklassen);
            $fachklassen->addFachlehrer($this);
        }

        return $this;
    }

    public function removeFachklassen(Klasse $fachklassen): static
    {
        if ($this->fachklassen->removeElement($fachklassen)) {
            $fachklassen->removeFachlehrer($this);
        }

        return $this;
    }
}
