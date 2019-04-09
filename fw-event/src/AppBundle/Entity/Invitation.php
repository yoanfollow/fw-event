<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 08/04/2019
 * Time: 14:29
 */

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="Invitation")
 */
class Invitation
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User", cascade={"persist"})
     */
    private $invitationUser;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Event", cascade={"persist"})
     */
    private $invitationEvent;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $accepted;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @Assert\DateTime()
     */
    private $limitedDate;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @Assert\DateTime()
     */
    private $createDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $updateDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $deleteDate;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getInvitationUser()
    {
        return $this->invitationUser;
    }

    /**
     * @param mixed $invitationUser
     */
    public function setInvitationUser($invitationUser)
    {
        $this->invitationUser = $invitationUser;
    }

    /**
     * @return mixed
     */
    public function getInvitationEvent()
    {
        return $this->invitationEvent;
    }

    /**
     * @param mixed $invitationEvent
     */
    public function setInvitationEvent($invitationEvent)
    {
        $this->invitationEvent = $invitationEvent;
    }

    /**
     * @return mixed
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    /**
     * @param mixed $accepted
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;
    }

    /**
     * @return mixed
     */
    public function getLimitedDate()
    {
        return $this->limitedDate;
    }

    /**
     * @param mixed $limitedDate
     */
    public function setLimitedDate($limitedDate)
    {
        $this->limitedDate = $limitedDate;
    }

    /**
     * @return mixed
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param mixed $createDate
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    /**
     * @return mixed
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param mixed $updateDate
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    }

    /**
     * @return mixed
     */
    public function getDeleteDate()
    {
        return $this->deleteDate;
    }

    /**
     * @param mixed $deleteDate
     */
    public function setDeleteDate($deleteDate)
    {
        $this->deleteDate = $deleteDate;
    }


}