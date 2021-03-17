<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiFilter;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Repository\IndividualDataCategoryRepository;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ORM\Entity(repositoryClass=IndividualDataCategoryRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"read:dataCategory"}},
 *      denormalizationContext={"groups"={"write:dataCategory"}},
 *      itemOperations={
 *          "GET"
 *      }
 * )
 * @ApiFilter(SearchFilter::class, properties={"code": "exact"})
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
     * @Groups({"read:data"})
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity=ProfilModelData::class, mappedBy="individualDataCategory")
     */
    private $ProfilModelData;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="category")
     */
    private $documents;

    public function __construct()
    {
        $this->ProfilModelData = new ArrayCollection();
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
            $document->setCategory($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getCategory() === $this) {
                $document->setCategory(null);
            }
        }

        return $this;
    }
}
