<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\EntityHook\AutoCreatedAtInterface;
use App\EntityHook\AutoUpdatedAtInterface;
use App\Helpers\DateHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use App\Api\Filter\ExpiredInvitationFilter;


/**
 * @ORM\Entity(repositoryClass="App\Repository\InvitationRepository")
 * @UniqueEntity(
 *     fields={"event", "recipient"},
 *     errorPath="recipient",
 *     message="User is already invited to this event"
 * )
 * @ApiResource(
 *      collectionOperations={"get", "post"={
 *          "defaults"={"confirmed"=false}
 *      }},
 *      itemOperations={"get","put","delete"},
 *      attributes={
 *          "normalization_context"={"groups"={"read_invitation"}},
 *          "denormalization_context"={"groups"={"write_invitation"}}
 *     },
 *     subresourceOperations={
 *          "api_events_participants_get_subresource"={
 *              "method"="get",
 *              "normalization_context"={"groups"={"read_event"}}
 *          }
 *     }
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "id": "exact",
 *     "event": "exact",
 *     "receiver": "exact",
 * })
 * @ApiFilter(DateFilter::class, properties={"expireAt"}, strategy=DateFilter::EXCLUDE_NULL)
 * @ApiFilter(ExpiredInvitationFilter::class)
 * @ApiFilter(BooleanFilter::class, properties={"confirmed"})
 * @ApiFilter(OrderFilter::class, properties={"id", "rate", "createdAt"}, arguments={"orderParameterName"="order"})
 */
class Invitation implements AutoCreatedAtInterface, AutoUpdatedAtInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read_invitation", "read_event"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="participants")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read_invitation", "write_invitation"})
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read_invitation", "write_invitation", "post_event", "read_event"})
     * @Assert\NotBlank
     */
    private $recipient;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"read_invitation", "read_event"})
     */
    private $confirmed;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read_invitation", "write_invitation", "post_event", "read_event"})
     * @Assert\GreaterThan("today")
     */
    private $expireAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read_invitation", "read_event"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    public function __construct()
    {
        $this->confirmed = false;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function setRecipient(?User $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getConfirmed(): ?bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    public function getExpireAt(): ?\DateTimeInterface
    {
        return $this->expireAt;
    }

    public function setExpireAt(?\DateTimeInterface $expireAt): self
    {
        $this->expireAt = $expireAt;

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
     * @Groups({"read_invitation"})
     */
    public function isExpired()
    {
        if (!$this->expireAt) {
            return DateHelper::getToday() > $this->expireAt;
        }
        return DateHelper::getToday() > $this->getEvent()->getEndAt();
    }
}
