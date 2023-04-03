<?php

namespace App\Entity;

use App\Repository\TraductionSourceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TraductionSourceRepository::class)
 */
class TraductionSource
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $projet_id;

    /**
     * @ORM\Column(type="text")
     */
    private $source;

    /**
     * @ORM\Column(type="boolean")
     */
    private $traduit;

    /**
     * @ORM\Column(type="boolean")
     */
    private $bloque;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProjetId(): ?int
    {
        return $this->projet_id;
    }

    public function setProjetId(int $projet_id): self
    {
        $this->projet_id = $projet_id;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getTraduit(): ?bool
    {
        return $this->traduit;
    }

    public function setTraduit(bool $traduit): self
    {
        $this->traduit = $traduit;

        return $this;
    }

    public function getBloque(): ?bool
    {
        return $this->bloque;
    }

    public function setBloque(bool $bloque): self
    {
        $this->bloque = $bloque;

        return $this;
    }
}
