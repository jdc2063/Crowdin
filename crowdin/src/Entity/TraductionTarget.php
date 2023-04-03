<?php

namespace App\Entity;

use App\Repository\TraductionTargetRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TraductionTargetRepository::class)
 */
class TraductionTarget
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $lang_code;

    /**
     * @ORM\Column(type="integer")
     */
    private $traduction_source_id;

    /**
     * @ORM\Column(type="text")
     */
    private $traduction;

    /**
     * @ORM\Column(type="integer")
     */
    private $traducteur_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLangCode(): ?string
    {
        return $this->lang_code;
    }

    public function setLangCode(string $lang_code): self
    {
        $this->lang_code = $lang_code;

        return $this;
    }

    public function getTraductionSourceId(): ?int
    {
        return $this->traduction_source_id;
    }

    public function setTraductionSourceId(int $traduction_source_id): self
    {
        $this->traduction_source_id = $traduction_source_id;

        return $this;
    }

    public function getTraduction(): ?string
    {
        return $this->traduction;
    }

    public function setTraduction(string $traduction): self
    {
        $this->traduction = $traduction;

        return $this;
    }

    public function getTraducteurId(): ?int
    {
        return $this->traducteur_id;
    }

    public function setTraducteurId(int $traducteur_id): self
    {
        $this->traducteur_id = $traducteur_id;

        return $this;
    }
}
