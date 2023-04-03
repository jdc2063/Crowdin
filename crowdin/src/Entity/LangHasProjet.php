<?php

namespace App\Entity;

use App\Repository\LangHasProjetRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LangHasProjetRepository::class)
 * @ORM\Table(name="lang_has_projet")
 */
class LangHasProjet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $lang;

    /**
     * @ORM\Column(type="integer")
     */
    private $id_projet;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(string $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    public function getIdProjet(): ?int
    {
        return $this->id_projet;
    }

    public function setIdProjet(int $id_projet): self
    {
        $this->id_projet = $id_projet;

        return $this;
    }
}
