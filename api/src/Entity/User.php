<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\EntityHook\AutoCreatedAtInterface;
use App\EntityHook\AutoUpdatedAtInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     fields={"username"},
 *     errorPath="username",
 *     message="Username already used"
 * )
 * @UniqueEntity(
 *     fields={"email"},
 *     errorPath="email",
 *     message="Email already used"
 * )
 * @ORM\Table(
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="uq_username_idx", columns={"username"}),
 *          @ORM\UniqueConstraint(name="uq_email_idx", columns={"email"})
 *      }
 * )
 * @ApiResource(
 *      collectionOperations={
 *          "get",
 *          "register"={
 *              "route_name"="register",
 *              "swagger_context"={
 *                  "parameters"={
 *                      {
 *                          "name"="body",
 *                          "in"="body",
 *                          "type"="json",
 *                          "schema"={
 *                              "properties"={
 *                                  "username"={"type"="string"},
 *                                  "email"={"type"="string"},
 *                                  "plainPassword"={"type"="string"}
 *                              }
 *                          }
 *                      }
 *                  },
 *                  "consumes"={"application/json"},
 *                  "produces"={"application/json"},
 *              }
 *          },
 *          "auth"={
 *              "route_name"="login_check",
 *              "swagger_context"={
 *                  "parameters"={
 *                      {
 *                          "name"="body",
 *                          "in"="body",
 *                          "schema"={
 *                              "properties"={
 *                                  "username"={"type"="string", "example"="jquinson"},
 *                                  "password"={"type"="string", "example"="test"}
 *                              }
 *                          }
 *                      }
 *                  }
 *              }
 *          },
 *     },
 *      itemOperations={
 *          "get",
 *          "put"={
 *              "access_control"="is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object == user)",
 *              "denormalization_context"={"groups"={"user:put"}},
 *              "validation_groups"={"validate:user:put", "Default"}
 *          }
 *     },
 *     attributes={
 *          "normalization_context"={"groups"={"user:read"}},
 *          "pagination_client_items_per_page"=true,
 *          "maximum_items_per_page"=100
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "username": "partial", "email": "partial"})
 * @ApiFilter(OrderFilter::class, properties={"id", "username", "email", "createdAt"}, arguments={"orderParameterName"="order"})
 * @Vich\Uploadable
 */
class User implements UserInterface, AutoCreatedAtInterface, AutoUpdatedAtInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"user:read", "event:read:user", "comment:read:user", "invitation:read:user", "event:read:comment", "event:read:invitation"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=175)
     * @Groups({"user:read", "user:put", "event:read:user", "comment:read:user", "invitation:read:user", "event:read:comment", "event:read:invitation"})
     * @Assert\NotBlank
     * @Assert\Email
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user:put"})
     * @Assert\NotBlank(message="Password cannot be blank", groups={"validate:user:put"})
     */
    private $password;

    /**
     * @var array
     * @ORM\Column(type="array")
     * @Groups({"user:read"})
     */
    private $roles;

    /**
     * @ORM\Column(type="string", length=175)
     * @Groups({"user:read", "event:read:user", "comment:read:user", "invitation:read:user", "event:read:comment", "event:read:invitation"})
     * @Assert\NotBlank(message="Username cannot be blank")
     */
    private $username;

    /**
     * @Groups({"user:read", "user:put", "event:read:user", "comment:read:user", "invitation:read:user", "event:read:comment", "event:read:invitation"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Media")
     */
    private $avatar;

    /**
     * @var string $avatarUrl
     * @Groups({"user:read", "comment:read:user", "invitation:read:user", "event:read:comment", "event:read:invitation"})
     */
    private $avatarUrl;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Invitation", mappedBy="recipient", orphanRemoval=true, cascade={"remove"}, fetch="LAZY")
     * @Groups({"user:read:invitation"})
     * @ApiSubresource(maxDepth=1)
     */
    private $invitations;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"user:read", "invitation:read:user"})
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
        $this->roles = ['ROLE_USER'];
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
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

    public function getRoles(): array
    {
        $roles = $this->roles;
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }

        return $roles;
    }

    public function setRoles(array $roles): self
    {
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }

        $this->roles = $roles;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getAvatar(): ?Media
    {
        return $this->avatar;
    }

    public function setAvatar(?Media $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(string $avatarUrl): void
    {
        $this->avatarUrl = $avatarUrl;
    }

    public function getInvitations()
    {
        return $this->invitations;
    }

    public function setInvitations($invitations): void
    {
        $this->invitations = $invitations;
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

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {

    }
}
