<?php

namespace App\Entity;

use App\Repository\IndividualDataCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IndividualDataCategoryRepository::class)
 */
class IndividualDataCategory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity=ProfilModelData::class, mappedBy="individualDataCategory")
     */
    private $ProfilModelData;

    public function __construct()
    {
        $this->ProfilModelData = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection|ProfilModelData[]
     */
    public function getProfilModelData(): Collection
    {
        return $this->ProfilModelData;
    }

    public function addProfilModelData(ProfilModelData $profilModelData): self
    {
        if (!$this->ProfilModelData->contains($profilModelData)) {
            $this->ProfilModelData[] = $profilModelData;
            $profilModelData->setIndividualDataCategory($this);
        }

        return $this;
    }

    public function removeProfilModelData(ProfilModelData $profilModelData): self
    {
        if ($this->ProfilModelData->removeElement($profilModelData)) {
            // set the owning side to null (unless already changed)
            if ($profilModelData->getIndividualDataCategory() === $this) {
                $profilModelData->setIndividualDataCategory(null);
            }
        }

        return $this;
    }
}
