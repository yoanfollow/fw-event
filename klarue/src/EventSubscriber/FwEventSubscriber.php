<?php
// api/src/EventSubscriber/BookMailSubscriber.php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Event;
use App\Entity\Invitation;
use App\Entity\Location;
use App\Entity\Comment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;


final class FwEventSubscriber implements EventSubscriberInterface
{
    private $security;
    private $mailer;

    public function __construct(Security $security, \Swift_Mailer $mailer)
    {
        $this->security = $security;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['sendRelance', EventPriorities::POST_READ],
            KernelEvents::VIEW => ['preWriteEvent', EventPriorities::PRE_WRITE],
        ];
    }


    public function sendRelance(GetResponseForControllerResultEvent $event)
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $routeName = $event->getRequest()->attributes->get("_route");
        $now = new \DateTime("now");
        if ($method == Request::METHOD_GET && $entity instanceof Invitation && $routeName == "api_events_participants_get_subresource"){
            if ($entity->getIsConfirmed() == false && $now < $entity->getLimitedAt())
            {
                /** Send Mail **/
                try{
                    $message = (new \Swift_Message('A new book has been added'))
                        ->setFrom('system@example.com')
                        ->setTo($entity->getToUser()->getEmail())
                        ->setBody(sprintf('The book #%d has been added.', $entity->getId()));

                    $this->mailer->send($message);
                }
                catch(\Exception $e)
                {

                }

            }
        }
    }

    public function preWriteEvent(GetResponseForControllerResultEvent $event)
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if ($method == Request::METHOD_POST)
        {
            $usr= $this->security->getToken()->getUser();
            $entity->setOwner($usr);
            if ($entity instanceof Invitation)
            {
                if (!$entity->getLimitedAt())
                {
                    $entity->setLimitedAt($entity->getEvent()->getBeginAt());
                }
            }

        }


        if ($method == Request::METHOD_PUT)
        {
            $entity->setUpdatedAt(new \DateTime("now"));
        }




        /*if (!$entity instanceof Invitation || Request::METHOD_PUT !== $method) {
         return;
        }*/



        /*$message = (new \Swift_Message('A new book has been added'))
            ->setFrom('system@example.com')
            ->setTo('contact@les-tilleuls.coop')
            ->setBody(sprintf('The book #%d has been added.', $book->getId()));

        $this->mailer->send($message);*/
    }
}