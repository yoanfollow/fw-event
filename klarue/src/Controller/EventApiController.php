<?php

namespace App\Controller;

use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EventApiController extends AbstractController
{

    public function __construct()
    {
    }

    /**
     * @Route(
     *     name="get_my_events",
     *     path="/myevents",
     *     methods={"GET"},
     * )
     * @return Array|null
     */
    public function myEvents(Request $request)
    {
        var_dump($request->attributes->get("_route"));
     /*   $repo = $this->getDoctrine()->getRepository("App\Entity\Event");
        $user = $this->getUser();
        $datas = $repo->findMyEvents($user);*/

        return $datas;
    }






}