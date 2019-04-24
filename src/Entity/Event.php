<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={
 *          "get"={
 *              "access_control"="is_granted('ROLE_USER')"
 *          },
 *          "post"={
 *              "method"="POST",
 *              "validation_groups"={"write"},
*               "controller"="App\Controller\EventController::post",
*               "datetime_format"="Y-m-d H:i:s"
 *          }
 *      },
 *     itemOperations={
 *          "get",
 *          "delete"={
 *              "method"="DELETE",
 *              "path"="/events/{id}",
 *              "controller"="App\Controller\EventController::delete",
 *              "defaults"={"_api_receive"=false},
 *          },
 *      },
 *
 * )
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 */
class Event
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read", "write"})
     * @Assert\NotBlank(groups={"postValidation"})
     */
    private $Name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read", "write"})
     * @Assert\NotBlank(groups={"postValidation"})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read", "write"})
     * @Assert\DateTime(groups={"postValidation"})
     * @Assert\NotNull(groups={"postValidation"})
     */
    private $start_date;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read", "write"})
     * @Assert\DateTime()
     * @Assert\NotBlank()
     */
    private $end_date;

    /**
     * @var User The organizer
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="organizer_id", referencedColumnName="id", nullable=false)
     *  @Groups({"read"})
     * @Assert\NotNull()
     */
    private $organizer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Place")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull()
     */
    private $place;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creation_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $update_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $delete_date;

    public function __construct()
    {
        if ($this->creation_date === null) {
            $this->creation_date = new \DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTimeInterface $start_date): self
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;

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

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creation_date;
    }

    public function setCreationDate(\DateTimeInterface $creation_date): self
    {
        $this->creation_date = $creation_date;

        return $this;
    }

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->update_date;
    }

    public function setUpdateDate(?\DateTimeInterface $update_date): self
    {
        $this->update_date = $update_date;

        return $this;
    }

    public function getDeleteDate(): ?\DateTimeInterface
    {
        return $this->delete_date;
    }

    public function setDeleteDate(?\DateTimeInterface $delete_date): self
    {
        $this->delete_date = $delete_date;

        return $this;
    }
}
