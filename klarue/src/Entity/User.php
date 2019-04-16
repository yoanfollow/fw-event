<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;


use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity
 * @ApiResource(
 *     attributes={
 *          "normalization_context"={"groups"={"get"}},
 *     },
 *     collectionOperations={"get"},
 *     itemOperations={"get"}
 * )
 */
class User implements UserInterface
{

    /**
     * @Groups({"invite"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255,unique=true ,nullable=true)
     * @Groups({"get","invite"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=500,nullable=false)
     *
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255,unique=true ,nullable=false)
     * @Groups({"get","get_events","invite"})
     */
    private $username;

    /**
     * @ORM\Column(type="array")
     */
    private $roles;


    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Groups({"get"})
     */
    private $avatar;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="organisator")
     * @Groups({"get"})
     * @ApiSubresource()
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Invitation", mappedBy="to_user")
     * @Groups({"get"})
     * @ApiSubresource()
     */
    private $invitations;

    /**
     * @return mixed
     */
    public function getInvitations()
    {
        return $this->invitations;
    }


    /**
     * @return mixed
     */
    public function getEvents()
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setOrganisator($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getEvent() === $this) {
                $event->setEvent(null);
            }
        }

        return $this;
    }

    public function addInvitation(Invitation $invitation): self
    {
        if (!$this->invitations->contains($invitation)) {
            $this->invitations[] = $invitation;
            $invitation->setToUser($this);
        }

        return $this;
    }

    public function removeInvitation(Invitation $invitation): self
    {
        if ($this->invitations->contains($invitation)) {
            $this->invitations->removeElement($invitation);
            // set the owning side to null (unless already changed)
            if ($invitation->getToUser() === $this) {
                $invitation->setToUser(null);
            }
        }

        return $this;
    }



    /**
     * @param mixed $events
     *
     * @return User
     */
    public function setEvents($events)
    {
        $this->events = $events;
        return $this;
    }

    /**
     * Constructeur
     */
    public function __construct($username,$roles = null)
    {
        $this->username = $username;
        $this->roles = ["ROLE_USER"];
        //$this->created_at = new \DateTime("now");
    }



    public function getSalt()
    {
        return null;
    }


    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * Get Id
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }


    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles()
    {
        // TODO: Implement getRoles() method.
        return ["ROLE_USER"];
    }


    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.

    }

    /**
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param mixed $avatar
     */
    public function setAvatar($avatar): void
    {
        $this->avatar = $avatar;
    }


}