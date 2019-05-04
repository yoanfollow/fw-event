<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\EntityHook\AutoCreatedAtInterface;
use App\EntityHook\AutoUpdatedAtInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 * @UniqueEntity(
 *     fields={"author", "event"},
 *     errorPath="author",
 *     message="User already left a comment for this event"
 * )
 * @ApiResource(
 *     collectionOperations={"post"},
 *     itemOperations={"get","put","delete"},
 *     attributes={
 *          "normalization_context"={"groups"={"read_comment"}},
 *          "denormalization_context"={"groups"={"write"}}
 *     },
 *     subresourceOperations={
 *          "api_events_comments_get_subresource"={
 *              "method"="get",
 *              "normalization_context"={"groups"={"read_event"}}
 *          }
 *     }
 * )
 */
class Comment implements AutoCreatedAtInterface, AutoUpdatedAtInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read_comment", "read_event"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read_comment", "read_event"})
     * Author is automatically filled in entity hook
     */
    private $author;

    /**
     * @ORM\Column(type="text")
     * @Groups({"read_comment", "write", "read_event"})
     */
    private $content;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(
     *      min = 1,
     *      max = 5,
     *      minMessage = "Rate must at least {{ limit }}",
     *      maxMessage = "Rate cannot be greater than {{ limit }}"
     * )
     * @Groups({"read_comment", "write", "read_event"})
     */
    private $rate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read_comment", "write"})
     */
    private $event;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read_comment", "read_event"})
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(?int $rate): self
    {
        $this->rate = $rate;

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

}
