<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Validator\Constraints\ConfirmedProperties;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     attributes={"access_control"="is_granted('ROLE_USER')"},
 *     normalizationContext={"groups"={"invite"}},
 *     collectionOperations={
 *         "get",
 *         "post",
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"access_control"="is_granted('ROLE_USER') and (object.to_user == user or object.owner == user)"},
 *         "delete"={"access_control"="is_granted('ROLE_USER') and object.owner == user"},
 *     }
 * )
 * @ApiFilter(BooleanFilter::class, properties={"is_confirmed"})
 * @ORM\Entity(repositoryClass="App\Repository\InvitationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Invitation extends Entity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull()
     * @ApiSubresource()
     * @Groups({"invite"})
    */
    public $to_user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_confirmed = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $limited_at;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="participants")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull()
     * @Groups({"get","invite"})
    */
    private $event;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getToUser(): ?User
    {
        return $this->to_user;
    }

    public function setToUser(?User $to_user): self
    {
        $this->to_user = $to_user;

        return $this;
    }

    public function getIsConfirmed(): ?bool
    {
        return $this->is_confirmed;
    }

    public function setIsConfirmed(bool $is_confirmed): self
    {
        $this->is_confirmed = $is_confirmed;

        return $this;
    }

    public function getLimitedAt(): ?\DateTimeInterface
    {
        return $this->limited_at;
    }

    public function setLimitedAt(?\DateTimeInterface $limited_at): self
    {
        $this->limited_at = $limited_at;

        return $this;
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

    /**
     * @ORM\PreUpdate
     * @param PreUpdateEventArgs.
     */
    public function validate(PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField("is_confirmed") && $event->getNewValue("is_confirmed") == true){
            $now = new \DateTime("now");
            if ($now > $this->getLimitedAt())
            {
                throw new \Exception("Invitation expirée.");
            }
        }
    }
}
