<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EventPhoto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $filename = null;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    public function getId(): ?int { return $this->id; }
    public function getFilename(): ?string { return $this->filename; }
    public function setFilename(string $filename): static { $this->filename = $filename; return $this; }
    public function getEvent(): ?Event { return $this->event; }
    public function setEvent(?Event $event): static { $this->event = $event; return $this; }
}
