<?php

namespace App\Entity;

use App\Repository\InvitationCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InvitationCategoryRepository::class)
 */
class InvitationCategory
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
     * @ORM\OneToMany(targetEntity=Invitation::class, mappedBy="invitationCategory")
     */
    private $invitation;

    public function __construct()
    {
        $this->invitation = new ArrayCollection();
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
     * @return Collection|Invitation[]
     */
    public function getInvitation(): Collection
    {
        return $this->invitation;
    }

    public function addInvitation(Invitation $invitation): self
    {
        if (!$this->invitation->contains($invitation)) {
            $this->invitation[] = $invitation;
            $invitation->setInvitationCategory($this);
        }

        return $this;
    }

    public function removeInvitation(Invitation $invitation): self
    {
        if ($this->invitation->removeElement($invitation)) {
            // set the owning side to null (unless already changed)
            if ($invitation->getInvitationCategory() === $this) {
                $invitation->setInvitationCategory(null);
            }
        }

        return $this;
    }
}
