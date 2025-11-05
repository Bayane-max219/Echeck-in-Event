<?php

namespace App\Entity;

use App\Repository\CheckInRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CheckInRepository::class)]
class CheckIn
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['checkin:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['checkin:read'])]
    private ?\DateTimeImmutable $checkedInAt = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['checkin:read'])]
    private ?string $checkedInBy = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['checkin:read'])]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'checkIns')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'checkIns')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $participant = null;

    public function __construct()
    {
        $this->checkedInAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCheckedInAt(): ?\DateTimeImmutable
    {
        return $this->checkedInAt;
    }

    public function setCheckedInAt(\DateTimeImmutable $checkedInAt): static
    {
        $this->checkedInAt = $checkedInAt;
        return $this;
    }

    public function getCheckedInBy(): ?string
    {
        return $this->checkedInBy;
    }

    public function setCheckedInBy(?string $checkedInBy): static
    {
        $this->checkedInBy = $checkedInBy;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
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
}