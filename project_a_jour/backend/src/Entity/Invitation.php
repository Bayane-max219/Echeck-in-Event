<?php

namespace App\Entity;

use App\Repository\InvitationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InvitationRepository::class)]
class Invitation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['invitation:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['invitation:read'])]
    private ?string $status = 'sent';

    #[ORM\Column]
    #[Groups(['invitation:read'])]
    private ?\DateTimeImmutable $sentAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['invitation:read'])]
    private ?\DateTimeImmutable $confirmedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['invitation:read'])]
    private ?string $token = null;

    #[ORM\ManyToOne(inversedBy: 'invitations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'invitations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $participant = null;

    public function __construct()
    {
        $this->sentAt = new \DateTimeImmutable();
        $this->token = $this->generateToken();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeImmutable $sentAt): static
    {
        $this->sentAt = $sentAt;
        return $this;
    }

    public function getConfirmedAt(): ?\DateTimeImmutable
    {
        return $this->confirmedAt;
    }

    public function setConfirmedAt(?\DateTimeImmutable $confirmedAt): static
    {
        $this->confirmedAt = $confirmedAt;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): static
    {
        $this->token = $token;
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

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): static
    {
        $this->participant = $participant;
        return $this;
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function confirm(): void
    {
        $this->status = 'confirmed';
        $this->confirmedAt = new \DateTimeImmutable();
    }
}