<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IndividualDataRepository::class)
 */
class IndividualData
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Individual::class, inversedBy="individualData")
     */
    private $individual;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $data;

    /**
     * @ORM\ManyToOne(targetEntity=ProfilModelData::class, inversedBy="IndividualData")
     */
    private $profilModelData;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIndividual(): ?Individual
    {
        return $this->individual;
    }

    public function setIndividual(?Individual $individual): self
    {
        $this->individual = $individual;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getProfilModelData(): ?ProfilModelData
    {
        return $this->profilModelData;
    }

    public function setProfilModelData(?ProfilModelData $profilModelData): self
    {
        $this->profilModelData = $profilModelData;

        return $this;
    }
}
