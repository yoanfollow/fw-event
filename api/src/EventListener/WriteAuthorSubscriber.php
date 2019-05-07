<?php


namespace App\EventListener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Comment;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Set author in any entities authored by user (Comment, Event...)
 * Set author before validation (to trigger UniqueEntity validator)
 */
class WriteAuthorSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * CurrentUserSubscriber constructor.
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [['setCurrentUser', EventPriorities::PRE_VALIDATE]],
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function setCurrentUser(GetResponseForControllerResultEvent $event)
    {
        $object = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        // Only for Event and Comment entities
        if ((!$object instanceof Event && !$object instanceof Comment) || Request::METHOD_POST !== $method) {
            return;
        }

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        if ($object instanceof Event) {
            $object->setOrganizer($user);
        } else if ($object instanceof Comment) {
            $object->setAuthor($user);
        }

        $event->setControllerResult($object);
    }
}
