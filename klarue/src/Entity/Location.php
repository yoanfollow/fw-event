<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     attributes={"access_control"="is_granted('ROLE_USER')"},
 *     collectionOperations={
 *         "get",
 *         "post",
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"access_control"="is_granted('ROLE_USER') and object.owner == user"},
 *         "delete"={"access_control"="is_granted('ROLE_USER') and object.owner == user"}
 *     },
 * )
 * @ORM\Entity(repositoryClass="App\Repository\LocationRepository")
 */
class Location extends Entity
{
    /**
     * @Groups({"event"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=300)
     * @Assert\NotBlank(
     *     message = "Le nom du lieu doit être renseigné."
     * )
     * @Groups({"event"})
     */
    private $name;

    /**
     * @ORM\Column(name="street_number",type="string",length=255)
     * @Assert\NotBlank(
     *     message = "Le numéro de la rue doit être renseigné."
     * )
     * @Groups({"event"})
     */
    private $streetNumber;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *     message = "La ville doit être renseigné."
     * )
     * @Groups({"event"})
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *     message = "L'adresse doit être renseigné."
     * )
     * @Groups({"event"})
     */
    private $address;

    /**
     * @ORM\Column(name="postal_code",type="string",length=5)
     * @Assert\NotBlank(
     *     message = "Le code postal doit être renseigné."
     * )
     * @Assert\Length(
     *     min = 5,
     *     max = 5,
     *     minMessage = "Le code postal doit comporter 5 caractères.",
     *     maxMessage = "Le code postal doit comporter 5 caractères."
     * )
     * @Groups({"event"})
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *     message = "Le pays doit être renseigné."
     * )
     * @Groups({"event"})
     */
    private $country;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    public function setStreetNumber(string $streetNumber): self
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function __construct()
    {
        $this->created_at = new \Datetime("now");
    }
}
