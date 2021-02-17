<?php

namespace App\Entity;

use App\Repository\ProfilesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProfilesRepository::class)
 */
class Profiles
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
     * @ORM\ManyToMany(targetEntity=ProfilModelData::class, inversedBy="profiles")
     */
    private $profileModelData;

    /**
     * @ORM\ManyToMany(targetEntity=Individual::class, mappedBy="profiles")
     */
    private $individuals;

    public function __construct()
    {
        $this->profileModelData = new ArrayCollection();
        $this->individuals = new ArrayCollection();
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
    public function getProfileModelData(): Collection
    {
        return $this->profileModelData;
    }

    public function addProfileModelData(ProfilModelData $profileModelData): self
    {
        if (!$this->profileModelData->contains($profileModelData)) {
            $this->profileModelData[] = $profileModelData;
        }

        return $this;
    }

    public function removeProfileModelData(ProfilModelData $profileModelData): self
    {
        $this->profileModelData->removeElement($profileModelData);

        return $this;
    }

    /**
     * @return Collection|Individual[]
     */
    public function getIndividuals(): Collection
    {
        return $this->individuals;
    }

    public function addIndividual(Individual $individual): self
    {
        if (!$this->individuals->contains($individual)) {
            $this->individuals[] = $individual;
            $individual->addProfile($this);
        }

        return $this;
    }

    public function removeIndividual(Individual $individual): self
    {
        if ($this->individuals->removeElement($individual)) {
            $individual->removeProfile($this);
        }

        return $this;
    }
}
