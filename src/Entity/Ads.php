<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AdsRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=AdsRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"read:ads"}},
 *      collectionOperations={"GET"},
 *      itemOperations={
 *          "GET",
 *      }
 * )
 */
class Ads
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:ads"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"read:ads"})
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity=Individual::class, inversedBy="ads")
     */
    private $individual;

    /**
     * @ORM\Column(type="float")
     * @Groups({"read:ads"})
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:ads"})
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity=AdsCategory::class, inversedBy="ads")
     * @Groups({"read:ads"})
     */
    private $adsCategory;

    /**
     * @ORM\OneToMany(targetEntity=AdsPictures::class, mappedBy="ads")
     * @Groups({"read:ads"})
     */
    private $adsPictures;

    public function __construct()
    {
        $this->adsPictures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAdsCategory(): ?AdsCategory
    {
        return $this->adsCategory;
    }

    public function setAdsCategory(?AdsCategory $adsCategory): self
    {
        $this->adsCategory = $adsCategory;

        return $this;
    }

    /**
     * @return Collection|AdsPictures[]
     */
    public function getAdsPictures(): Collection
    {
        return $this->adsPictures;
    }

    public function addAdsPicture(AdsPictures $adsPicture): self
    {
        if (!$this->adsPictures->contains($adsPicture)) {
            $this->adsPictures[] = $adsPicture;
            $adsPicture->setAds($this);
        }

        return $this;
    }

    public function removeAdsPicture(AdsPictures $adsPicture): self
    {
        if ($this->adsPictures->removeElement($adsPicture)) {
            // set the owning side to null (unless already changed)
            if ($adsPicture->getAds() === $this) {
                $adsPicture->setAds(null);
            }
        }

        return $this;
    }
}
