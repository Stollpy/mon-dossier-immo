<?php

namespace App\Entity;

use App\Repository\InvitationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InvitationRepository::class)
 */
class Invitation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=individual::class, inversedBy="invitations")
     */
    private $individual;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity=InvitationCategory::class, inversedBy="invitation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $invitationCategory;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIndividual(): ?individual
    {
        return $this->individual;
    }

    public function setIndividual(?individual $individual): self
    {
        $this->individual = $individual;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getInvitationCategory(): ?InvitationCategory
    {
        return $this->invitationCategory;
    }

    public function setInvitationCategory(?InvitationCategory $invitationCategory): self
    {
        $this->invitationCategory = $invitationCategory;

        return $this;
    }
}
