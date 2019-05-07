<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use App\EntityHook\AutoCreatedAtInterface;
use App\EntityHook\AutoUpdatedAtInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use App\Validator\EventFinished;


/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 * @UniqueEntity(
 *     fields={"author", "event"},
 *     errorPath="author",
 *     message="User already left a comment for this event"
 * )
 *
 * @ORM\Table(
 *     uniqueConstraints={@ORM\UniqueConstraint(name="uq_author_event_idx", columns={"author_id", "event_id"})}
 * )
 * @ApiResource(
 *     collectionOperations={"post"},
 *     itemOperations={
 *          "get"={
 *              "normalization_context"={"groups"={"comment:read", "comment:read:user"}}
 *          },
 *          "put",
 *          "delete"
 *      },
 *     attributes={
 *          "normalization_context"={"groups"={"comment:read"}},
 *          "denormalization_context"={"groups"={"comment:post"}},
 *          "pagination_client_items_per_page"=true,
 *          "maximum_items_per_page"=100
 *     },
 *     subresourceOperations={
 *          "api_events_comments_get_subresource"={
 *              "method"="get",
 *              "normalization_context"={"groups"={"event:read:comment"}}
 *          }
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "id": "exact",
 *     "author": "exact",
 *     "content": "partial",
 *     "rate": "exact",
 *     "event": "exact"
 * })
 * @ApiFilter(OrderFilter::class, properties={"id", "rate", "createdAt"}, arguments={"orderParameterName"="order"})
 */
class Comment implements AutoCreatedAtInterface, AutoUpdatedAtInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"comment:read", "event:read:comment"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"comment:read", "event:read:comment"})
     * Author is automatically filled in entity hook
     */
    private $author;

    /**
     * @ORM\Column(type="text")
     * @Groups({"comment:read", "comment:post", "event:read:comment"})
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
     * @Groups({"comment:read", "comment:post", "event:read:comment"})
     */
    private $rate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"comment:read", "comment:post"})
     * @Assert\NotBlank
     * @EventFinished(message="Event must be finished to post a comment")
     */
    private $event;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"comment:read", "event:read:comment"})
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
