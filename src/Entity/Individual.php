<?php

namespace App\Entity;

use App\Repository\IndividualRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IndividualRepository::class)
 */
class Individual
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="individual", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=IndividualData::class, mappedBy="individual")
     */
    private $individualData;

    public function __construct()
    {
        $this->individualData = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|IndividualData[]
     */
    public function getIndividualData(): Collection
    {
        return $this->individualData;
    }

    public function addIndividualData(IndividualData $individualData): self
    {
        if (!$this->individualData->contains($individualData)) {
            $this->individualData[] = $individualData;
            $individualData->setIndividual($this);
        }

        return $this;
    }

    public function removeIndividualData(IndividualData $individualData): self
    {
        if ($this->individualData->removeElement($individualData)) {
            // set the owning side to null (unless already changed)
            if ($individualData->getIndividual() === $this) {
                $individualData->setIndividual(null);
            }
        }

        return $this;
    }
}
