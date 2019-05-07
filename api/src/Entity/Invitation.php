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
 *      itemOperations={
 *          "get",
 *          "put"={
 *              "denormalization_context"={"groups"={"invitation:put"}}
 *          },
 *          "delete"
 *      },
 *      attributes={
 *          "normalization_context"={"groups"={"invitation:read", "invitation:read:event", "invitation:read:user"}},
 *          "denormalization_context"={"groups"={"invitation:write"}}
 *     },
 *     subresourceOperations={
 *          "api_events_participants_get_subresource"={
 *              "method"="get",
 *              "normalization_context"={"groups"={"event:read:invitation"}}
 *          },
 *          "api_users_invitations_get_subresource"={
 *              "method"="get",
 *              "normalization_context"={"groups"={"user:read:invitation", "user:read:event"}}
 *          }
 *     },
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "id": "exact",
 *     "event": "exact",
 *     "recipient": "exact",
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
     * @Groups({"invitation:read", "user:read:invitation", "event:read:invitation", "user:read:invitation"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="participants")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"invitation:read", "user:read:invitation", "invitation:write", "user:read:invitation"})
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="invitations")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"invitation:read", "invitation:write", "event:post", "event:read:invitation"})
     * @Assert\NotBlank
     */
    private $recipient;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"invitation:read", "invitation:put", "user:read:invitation", "event:read:invitation"})
     */
    private $confirmed;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"invitation:read", "user:read:invitation", "invitation:write", "event:post", "event:read:invitation", "user:read:invitation"})
     * @Assert\GreaterThan("today")
     */
    private $expireAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"invitation:read", "user:read:invitation", "event:read:invitation", "user:read:invitation"})
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
     * @Groups({"invitation:read", "event:read:invitation"})
     */
    public function isExpired()
    {
        if (!$this->expireAt) {
            return DateHelper::getToday() > $this->expireAt;
        }
        return DateHelper::getToday() > $this->getEvent()->getEndAt();
    }
}
