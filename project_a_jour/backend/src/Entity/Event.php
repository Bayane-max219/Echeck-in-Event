<?php

namespace App\Entity;
//ato manao calcule taux de présence na attendance rate
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Agent::class, cascade: ['persist', 'remove'])]
    private Collection $agents;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['event:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['event:read', 'event:write'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['event:read', 'event:write'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $location = null;

    #[ORM\Column(length: 50)]
    #[Groups(['event:read'])]
    private ?string $status = 'draft';

    #[ORM\Column]
    #[Groups(['event:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['event:read'])]
    private ?User $organizer = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Participant::class, cascade: ['persist', 'remove'])]
    private Collection $participants;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Invitation::class, cascade: ['persist', 'remove'])]
    private Collection $invitations;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: CheckIn::class, cascade: ['persist', 'remove'])]
    private Collection $checkIns;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: EventPhoto::class, cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $photos;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $menu = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $locale = null;


    public function __construct()
    {
        $this->agents = new ArrayCollection();
        $this->participants = new ArrayCollection();
        $this->invitations = new ArrayCollection();
        $this->checkIns = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->photos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;
        return $this;
    }

    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setEvent($this);
        }
        return $this;
    }

    public function removeParticipant(Participant $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            if ($participant->getEvent() === $this) {
                $participant->setEvent(null);
            }
        }
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
            $invitation->setEvent($this);
        }
        return $this;
    }

    public function removeInvitation(Invitation $invitation): static
    {
        if ($this->invitations->removeElement($invitation)) {
            if ($invitation->getEvent() === $this) {
                $invitation->setEvent(null);
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
            $checkIn->setEvent($this);
        }
        return $this;
    }

    public function removeCheckIn(CheckIn $checkIn): static
    {
        if ($this->checkIns->removeElement($checkIn)) {
            if ($checkIn->getEvent() === $this) {
                $checkIn->setEvent(null);
            }
        }
        return $this;
    }

    public function getParticipantCount(): int
    {
        return $this->participants->count();
    }

    public function getCheckedInCount(): int
    {
        return $this->checkIns->count();
    }
//fonction manao calcuel taux de présence attendance rate
    public function getAttendanceRate(): float
    {
        $totalParticipants = $this->getParticipantCount();
        if ($totalParticipants === 0) {
            return 0.0;
        }
        return ($this->getCheckedInCount() / $totalParticipants) * 100;
    }

    /**
     * @return Collection<int, Agent>
     */
    public function getAgents(): Collection
    {
        return $this->agents;
    }

    public function addAgent(Agent $agent): static
    {
        if (!$this->agents->contains($agent)) {
            $this->agents->add($agent);
            $agent->setEvent($this);
        }
        return $this;
    }

    public function removeAgent(Agent $agent): static
    {
        if ($this->agents->removeElement($agent)) {
            if ($agent->getEvent() === $this) {
                $agent->setEvent(null);
            }
        }
        return $this;
    }

    public function getPhotos(): \Doctrine\Common\Collections\Collection
    {
        return $this->photos;
    }

    public function addPhoto(\App\Entity\EventPhoto $photo): static
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setEvent($this);
        }
        return $this;
    }

    public function removePhoto(\App\Entity\EventPhoto $photo): static
    {
        if ($this->photos->removeElement($photo)) {
            if ($photo->getEvent() === $this) {
                $photo->setEvent(null);
            }
        }
        return $this;
    }

    public function getMenu(): ?string
    {
        return $this->menu;
    }

    public function setMenu(?string $menu): static
    {
        $this->menu = $menu;
        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): static
    {
        $this->locale = $locale;
        return $this;
    }
}