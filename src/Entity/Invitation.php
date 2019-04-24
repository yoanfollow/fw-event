<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\InvitationRepository")
 */
class Invitation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Invitation", inversedBy="event")
     */
    private $participant;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Invitation", mappedBy="participant")
     */
    private $event;

    /**
     * @ORM\Column(type="boolean")
     */
    private $confirmation;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $limit_date;

    public function __construct()
    {
        $this->event = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipant(): ?self
    {
        return $this->participant;
    }

    public function setParticipant(?self $participant): self
    {
        $this->participant = $participant;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getEvent(): Collection
    {
        return $this->event;
    }

    public function addEvent(self $event): self
    {
        if (!$this->event->contains($event)) {
            $this->event[] = $event;
            $event->setParticipant($this);
        }

        return $this;
    }

    public function removeEvent(self $event): self
    {
        if ($this->event->contains($event)) {
            $this->event->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getParticipant() === $this) {
                $event->setParticipant(null);
            }
        }

        return $this;
    }

    public function getConfirmation(): ?bool
    {
        return $this->confirmation;
    }

    public function setConfirmation(bool $confirmation): self
    {
        $this->confirmation = $confirmation;

        return $this;
    }

    public function getLimitDate(): ?\DateTimeInterface
    {
        return $this->limit_date;
    }

    public function setLimitDate(?\DateTimeInterface $limit_date): self
    {
        $this->limit_date = $limit_date;

        return $this;
    }
}
