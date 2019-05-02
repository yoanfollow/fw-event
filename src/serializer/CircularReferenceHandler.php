<?php

namespace App\serializer;

use App\Entity\Evenement;
use App\Entity\Utilisateur;
use Symfony\Component\Routing\RouterInterface;

class CircularReferenceHandler
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function __invoke($object)
    {
        switch ($object){
            case $object instanceof Evenement:
                return $object->getNom();
            //case $object instanceof Utilisateur:
              //  return $this->router->generate(name : 'utilisateur_show' ,['utilisateur'=> $object->getId()]);
        }
        return $object->getId();
    }

}