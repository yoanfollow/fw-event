<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\EntityHook\AutoCreatedAtInterface;
use App\EntityHook\AutoUpdatedAtInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;


/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @ApiResource(
 *      collectionOperations={
 *          "get",
 *          "post"={
 *              "denormalization_context"={"groups"={"event:post", "event:write"}},
 *              "validation_groups"={"Default", "postValidation"}
 *          }
 *     },
 *      itemOperations={
 *          "get",
 *          "put"={
 *              "access_control"="is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object.getOrganizer() == user)",
 *              "denormalization_context"={"groups"={"event:write"}},
 *              "validation_groups"={"Default", "putValidation"}
 *          },
 *          "delete"={
 *              "access_control"="is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object.getOrganizer() == user)"
 *          }
 *     },
 *      attributes={
 *          "normalization_context"={"groups"={"event:read"}},
 *          "force_eager"=false
 *      }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "id": "exact",
 *     "name": "partial",
 *     "organizer": "partial",
 *     "place": "exact",
 *     "description": "partial"
 * })
 * @ApiFilter(DateFilter::class, properties={"startAt", "endAt"}, strategy=DateFilter::EXCLUDE_NULL)
 * @ApiFilter(OrderFilter::class, properties={"id", "name", "startAt", "endAt", "createdAt"}, arguments={"orderParameterName"="order"})
 */
class Event implements AutoCreatedAtInterface, AutoUpdatedAtInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"event:read", "user:read:event"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"event:read", "event:write", "user:read:event", "invitation:read:event"})
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Groups({"event:read", "event:write", "user:read:event", "invitation:read:event"})
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"event:read", "invitation:read:event"})
     * Organizer is automatically filled in entity hook (See App\EntityHook)
     */
    private $organizer;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"event:read", "event:write", "user:read:event", "invitation:read:event"})
     */
    private $startAt;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\GreaterThan(propertyPath="startAt")
     * @Groups({"event:read", "event:write", "user:read:event", "invitation:read:event"})
     */
    private $endAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Invitation", mappedBy="event", orphanRemoval=true, cascade={"persist", "remove"})
     * @Groups({"event:read", "event:post"})
     * @Assert\Valid(groups={"postValidation"})
     * @ApiSubresource(maxDepth=1)
     */
    private $participants;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Place", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Valid(groups={"Default"})
     * @Groups({"event:read", "event:write", "user:read:event", "invitation:read:event"})
     */
    private $place;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="event", cascade={"remove"})
     * @Groups({"event:read"})
     * @ApiSubresource(maxDepth=1)
     */
    private $comments;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"event:read", "invitation:read:event"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"admin:user:read"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"admin:user:read"})
     */
    private $deletedAt;


    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): self
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * @return Place
     */
    public function getPlace() : ?Place
    {
        return $this->place;
    }

    /**
     * @param Place $place
     */
    public function setPlace($place): void
    {
        $this->place = $place;
    }

    /**
     * @return Collection|Invitation[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Invitation $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
            $participant->setEvent($this);
        }

        return $this;
    }

    public function removeParticipant(Invitation $participant): self
    {
        if ($this->participants->contains($participant)) {
            $this->participants->removeElement($participant);
            // set the owning side to null (unless already changed)
            if ($participant->getEvent() === $this) {
                $participant->setEvent(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setM($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getM() === $this) {
                $comment->setM(null);
            }
        }

        return $this;
    }
}
