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

    /**
     * @ORM\ManyToOne(targetEntity=Profiles::class, inversedBy="profiles")
     */
    private $parentProfile;

    /**
     * @ORM\OneToMany(targetEntity=Profiles::class, mappedBy="parentProfile")
     */
    private $profiles;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="profile")
     */
    private $documents;

    public function __construct()
    {
        $this->profileModelData = new ArrayCollection();
        $this->individuals = new ArrayCollection();
        $this->profiles = new ArrayCollection();
        $this->documents = new ArrayCollection();
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

    public function getParentProfile(): ?self
    {
        return $this->parentProfile;
    }

    public function setParentProfile(?self $parentProfile): self
    {
        $this->parentProfile = $parentProfile;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getProfiles(): Collection
    {
        return $this->profiles;
    }

    public function addProfile(self $profile): self
    {
        if (!$this->profiles->contains($profile)) {
            $this->profiles[] = $profile;
            $profile->setParentProfile($this);
        }

        return $this;
    }

    public function removeProfile(self $profile): self
    {
        if ($this->profiles->removeElement($profile)) {
            // set the owning side to null (unless already changed)
            if ($profile->getParentProfile() === $this) {
                $profile->setParentProfile(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setProfile($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getProfile() === $this) {
                $document->setProfile(null);
            }
        }

        return $this;
    }
}
