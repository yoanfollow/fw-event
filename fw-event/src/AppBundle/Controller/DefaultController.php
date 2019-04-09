<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/events", name="event")
     */
    public function eventAction(){
        return $this->render('pages/event/event.html.twig');
    }

    /**
     * @Route("/invitations", name="invitation")
     */
    public function invitationAction(){
        return $this->render('pages/invitation.html.twig');
    }
}