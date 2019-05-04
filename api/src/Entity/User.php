<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\EntityHook\AutoCreatedAtInterface;
use App\EntityHook\AutoUpdatedAtInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     fields={"username"},
 *     errorPath="username",
 *     message="Username already used"
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
 *                                  "username"={"type"="string"},
 *                                  "password"={"type"="string"}
 *                              }
 *                          }
 *                      }
 *                  }
 *              }
 *          },
 *     },
 *      itemOperations={"get", "put"},
 *      attributes={
 *          "normalization_context"={"groups"={"read"}},
 *          "denormalization_context"={"groups"={"write"}}
 *      }
 * )
 */
class User implements UserInterface, AutoCreatedAtInterface, AutoUpdatedAtInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read", "write", "read_event"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"write"})
     */
    private $password;

    /**
     * @var array
     * @ORM\Column(type="array")
     * @Groups({"read"})
     */
    private $roles;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read", "write", "read_event"})
     */
    private $username;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"read", "write"})
     */
    private $avatar;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read"})
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
        return $this->roles;
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

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

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

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {

    }
}
