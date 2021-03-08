<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AdsPicturesRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=AdsPicturesRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"read:ads"}},
 *      collectionOperations={"GET"},
 *      itemOperations={"GET"}
 * )
 */
class AdsPictures
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:ads"})
     */
    private $data;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mime_type;

    /**
     * @ORM\ManyToOne(targetEntity=Ads::class, inversedBy="adsPictures")
     */
    private $ads;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mime_type;
    }

    public function setMimeType(string $mime_type): self
    {
        $this->mime_type = $mime_type;

        return $this;
    }

    public function getAds(): ?Ads
    {
        return $this->ads;
    }

    public function setAds(?Ads $ads): self
    {
        $this->ads = $ads;

        return $this;
    }
}
