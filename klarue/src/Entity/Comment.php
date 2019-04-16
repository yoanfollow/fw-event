<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={"access_control"="is_granted('ROLE_USER')"},
 *     collectionOperations={
 *         "get",
 *         "post"
 *     },
 *     itemOperations={
 *         "get",
 *         "put" = {"access_control"="is_granted('ROLE_USER') and object.owner == user"},
 *         "delete" = {"access_control"="is_granted('ROLE_USER') and object.owner == user"}
 *     },
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Comment extends Entity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull(
     *     message = "Un commentaire doit être renseigné."
     * )
     * @Assert\NotBlank(
     *     message = "Un commentaire doit être renseigné."
     * )
     */
    private $comment;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(
     *      min = 1,
     *      max = 10,
     *      minMessage = "La note doit être comprise entre 1 et 10.",
     *      maxMessage = "La note doit être comprise entre 1 et 10."
     * )
     */
    private $note;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull()
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(
     *     message = "Un evènement doit être obligatoirement associé au commentaire."
     * )
     */
    private $event;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(?int $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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
     * @ORM\PrePersist()
     */
    public function validate()
    {
        $now = new \DateTime("now");
        if ($now < $this->event->getEndedAt())
        {
            throw new \Exception("Il n'est pas possible d'ajouter un commentaire pour le moment, l'évènement n'est pas terminé.");
        }
    }
}
