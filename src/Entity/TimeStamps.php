<?php

namespace App\Entity;

trait TimeStamps
{
    /**
     * @ORM\Column(type="datetime" , nullable=true)
     */
    private $dateDeCreation;

    /**
     * @ORM\Column(type="datetime" , nullable=true)
     */
    private $dateDeModification;

    /**
     * @ORM\PrePersist()
     */
    public function dateCreation()
    {
        $this->dateDeCreation = new \DateTime();
        $this->dateDeModification= new \DateTime();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function dateModification()
    {

        $this->dateDeModification= new \DateTime();
    }

}


