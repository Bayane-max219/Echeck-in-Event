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
