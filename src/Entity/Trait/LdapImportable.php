<?php
namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

trait LdapImportable {
    
    #[ORM\Column(nullable: true)]
    private ?bool $visible = false;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label = null;
    
    #[ORM\Column(length: 500, nullable: true)]
    private ?string $dn = null;
    
    public function isVisible(): ?bool
    {
        return $this->visible;
    }
    
    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;
        
        return $this;
    }
    
    public function getLabel(): ?string
    {
        return $this->label;
    }
    
    public function setLabel(?string $label): static
    {
        $this->label = $label;
        
        return $this;
    }
    
    public function getDn(): ?string
    {
        return $this->dn;
    }
    
    public function setDn(?string $dn): static
    {
        $this->dn = $dn;
        
        return $this;
    }
}