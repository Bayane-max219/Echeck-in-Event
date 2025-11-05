<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['participant:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['participant:read', 'participant:write'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Groups(['participant:read', 'participant:write'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 180)]
    #[Groups(['participant:read', 'participant:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['participant:read', 'participant:write'])]
    private ?string $phone = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['participant:read', 'participant:write'])]
    private ?string $company = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['participant:read', 'participant:write'])]
    private ?string $position = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['participant:read'])]
    private ?string $qrCode = null;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $confirmationToken = null;

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;
        return $this;
    }

    #[ORM\Column(length: 50)]
    #[Groups(['participant:read'])]
    private ?string $status = 'pending';

    #[ORM\Column]
    #[Groups(['participant:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\OneToMany(mappedBy: 'participant', targetEntity: Invitation::class, cascade: ['persist', 'remove'])]
    private Collection $invitations;

    #[ORM\OneToMany(mappedBy: 'participant', targetEntity: CheckIn::class, cascade: ['persist', 'remove'])]
    private Collection $checkIns;

    public function __construct()
    {
        $this->invitations = new ArrayCollection();
        $this->checkIns = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->qrCode = $this->generateQrCode();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): static
    {
        $this->company = $company;
        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getQrCode(): ?string
    {
        return $this->qrCode;
    }

    public function setQrCode(?string $qrCode): static
    {
        $this->qrCode = $qrCode;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;
        return $this;
    }

    public function getInvitations(): Collection
    {
        return $this->invitations;
    }

    public function addInvitation(Invitation $invitation): static
    {
        if (!$this->invitations->contains($invitation)) {
            $this->invitations->add($invitation);
            $invitation->setParticipant($this);
        }
        return $this;
    }

    public function removeInvitation(Invitation $invitation): static
    {
        if ($this->invitations->removeElement($invitation)) {
            if ($invitation->getParticipant() === $this) {
                $invitation->setParticipant(null);
            }
        }
        return $this;
    }

    public function getCheckIns(): Collection
    {
        return $this->checkIns;
    }

    public function addCheckIn(CheckIn $checkIn): static
    {
        if (!$this->checkIns->contains($checkIn)) {
            $this->checkIns->add($checkIn);
            $checkIn->setParticipant($this);
        }
        return $this;
    }

    public function removeCheckIn(CheckIn $checkIn): static
    {
        if ($this->checkIns->removeElement($checkIn)) {
            if ($checkIn->getParticipant() === $this) {
                $checkIn->setParticipant(null);
            }
        }
        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function isCheckedIn(): bool
    {
        return !$this->checkIns->isEmpty();
    }

    private function generateQrCode(): string
    {
        return 'QR_' . uniqid() . '_' . bin2hex(random_bytes(8));
    }
}