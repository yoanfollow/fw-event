<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EventController extends AbstractController
{
    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * @var User
     */
    protected $currentUser;

    public function __construct(EventRepository $eventRepository, TokenStorageInterface $tokenStorage)
    {
        $this->eventRepository = $eventRepository;
        $this->currentUser = $tokenStorage->getToken()->getUser();

    }

    public function post(Event $event)
    {

        die();
    }

    public function __invoke(Event $data)
    {
        die('ici');
    }

    public function delete(Request $request)
    {
        $event = $this->eventRepository->findById($request->get('id'), $this->currentUser);
        if ($event instanceof Event) {
            $this->eventRepository->delete($event);
        } else {
            throw new EntityNotFoundException("Event with the following id " . $request->get('id') . ' not found.');
        }
    }
}
