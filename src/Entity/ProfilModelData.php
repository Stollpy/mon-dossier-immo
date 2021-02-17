<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\IndividualDataCategory;
use Doctrine\Common\Collections\Collection;
use App\Repository\ProfilModelDataRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=ProfilModelDataRepository::class)
 */
class ProfilModelData
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
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity=IndividualData::class, mappedBy="profilModelData")
     */
    private $IndividualData;

    /**
     * @ORM\ManyToOne(targetEntity=IndividualDataCategory::class, inversedBy="ProfilModelData")
     * @ORM\JoinColumn(nullable=false)
     */
    private $individualDataCategory;

    /**
     * @ORM\ManyToMany(targetEntity=Profiles::class, mappedBy="profileModelData")
     */
    private $profiles;

    public function __construct()
    {
        $this->IndividualData = new ArrayCollection();
        $this->profiles = new ArrayCollection();
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|IndividualData[]
     */
    public function getIndividualData(): Collection
    {
        return $this->IndividualData;
    }

    public function addIndividualData(IndividualData $individualData): self
    {
        if (!$this->IndividualData->contains($individualData)) {
            $this->IndividualData[] = $individualData;
            $individualData->setProfilModelData($this);
        }

        return $this;
    }

    public function removeIndividualData(IndividualData $individualData): self
    {
        if ($this->IndividualData->removeElement($individualData)) {
            // set the owning side to null (unless already changed)
            if ($individualData->getProfilModelData() === $this) {
                $individualData->setProfilModelData(null);
            }
        }

        return $this;
    }

    public function getIndividualDataCategory(): ?IndividualDataCategory
    {
        return $this->individualDataCategory;
    }

    public function setIndividualDataCategory(?IndividualDataCategory $individualDataCategory): self
    {
        $this->individualDataCategory = $individualDataCategory;

        return $this;
    }

    /**
     * @return Collection|Profiles[]
     */
    public function getProfiles(): Collection
    {
        return $this->profiles;
    }

    public function addProfile(Profiles $profile): self
    {
        if (!$this->profiles->contains($profile)) {
            $this->profiles[] = $profile;
            $profile->addProfileModelData($this);
        }

        return $this;
    }

    public function removeProfile(Profiles $profile): self
    {
        if ($this->profiles->removeElement($profile)) {
            $profile->removeProfileModelData($this);
        }

        return $this;
    }
}
