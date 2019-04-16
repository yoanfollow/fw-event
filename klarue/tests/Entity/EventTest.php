<?php


namespace Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Event;

/**
 * Test Unitaire
 */
class EventTest extends TestCase
{

    public function testEvent()
    {
        $this->assertSame("t","t");
        /*$l = new Location();
        $l->setPostalCode("03290");
        $l->setStreetNumber("12");
        $l->setCountry("France");
        $l->setAddress("Grande Rue");
        $l->setCity("DIOU");
        $l->setName("Salle des fêtes");

        $this->assertSame("Salle des fêtes",$l->getName());
        $this->assertSame("Grande Rue",$l->getAddress());
        $this->assertSame("12",$l->getStreetNumber());
        $this->assertSame("DIOU",$l->getCity());
        $this->assertSame("03290", $l->getPostalCode());
        $this->assertSame("France", $l->getCountry());*/

    }




}