<?php


namespace Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Event;
use App\Entity\Location;

/**
 * Test Unitaire
 */
class CommentTest extends TestCase
{

    public function testComment()
    {
        /** Creation user */
        $u = new User("klarue");


        $l = new Location();
        $l->setPostalCode("03290");
        $l->setStreetNumber("12");
        $l->setCountry("France");
        $l->setAddress("Grande Rue");
        $l->setCity("DIOU");
        $l->setName("Salle des fêtes");
        $l->setOwner($u);

        $event = new Event();
        $event->setName("TEST Event");
        $event->setDescription("TEST Event");
        $event->setOwner($u);
        $event->setOrganisator($u);
        $event->setLocation($l);
        $event->getBeginAt(new \DateTime("2019-04-14 20:00:00"));
        $event->getEndedAt(new \DateTime("2019-04-14 20:30:00"));

        $comment = new Comment();
        $comment->setOwner($u);
        $comment->setComment("Commentaire test");
        $comment->setNote(5);
        $comment->setUser($u);
        $comment->setEvent($event);



        $this->assertSame("Commentaire test",$comment->getComment());
        $this->assertSame("TEST Event",$comment->getEvent()->getName());
        $this->assertSame(5,$comment->getNote());


    }




}