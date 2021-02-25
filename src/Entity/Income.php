<?php

namespace App\Entity;

use App\Repository\IncomeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IncomeRepository::class)
 */
class Income
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
     * @ORM\ManyToOne(targetEntity=IncomeType::class, inversedBy="incomes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="income")
     */
    private $document;

    /**
     * @ORM\ManyToOne(targetEntity=Individual::class, inversedBy="incomes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $individual;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity=IncomeYear::class, inversedBy="income")
     * @ORM\JoinColumn(nullable=false)
     */
    private $incomeYear;

    public function __construct()
    {
        $this->document = new ArrayCollection();
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

    public function getType(): ?IncomeType
    {
        return $this->type;
    }

    public function setType(?IncomeType $type): self
    {
        $this->type = $type;

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
            $document->setIncome($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->document->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getIncome() === $this) {
                $document->setIncome(null);
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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getIncomeYear(): ?IncomeYear
    {
        return $this->incomeYear;
    }

    public function setIncomeYear(?IncomeYear $incomeYear): self
    {
        $this->incomeYear = $incomeYear;

        return $this;
    }

}
