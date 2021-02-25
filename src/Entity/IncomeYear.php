<?php

namespace App\Entity;

use App\Repository\IncomeYearRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IncomeYearRepository::class)
 */
class IncomeYear
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
    private $code;

    /**
     * @ORM\OneToMany(targetEntity=Income::class, mappedBy="incomeYear", orphanRemoval=true)
     */
    private $income;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="incomeYear")
     */
    private $document;

    /**
     * @ORM\ManyToOne(targetEntity=Individual::class, inversedBy="incomeYears")
     */
    private $individual;

    public function __construct()
    {
        $this->income = new ArrayCollection();
        $this->document = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
     * @return Collection|Income[]
     */
    public function getIncome(): Collection
    {
        return $this->income;
    }

    public function addIncome(Income $income): self
    {
        if (!$this->income->contains($income)) {
            $this->income[] = $income;
            $income->setIncomeYear($this);
        }

        return $this;
    }

    public function removeIncome(Income $income): self
    {
        if ($this->income->removeElement($income)) {
            // set the owning side to null (unless already changed)
            if ($income->getIncomeYear() === $this) {
                $income->setIncomeYear(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocument(): Collection
    {
        return $this->document;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->document->contains($document)) {
            $this->document[] = $document;
            $document->setIncomeYear($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->document->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getIncomeYear() === $this) {
                $document->setIncomeYear(null);
            }
        }

        return $this;
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
}
