<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 08/04/2019
 * Time: 15:54
 */

namespace AppBundle\Controller\API;


use AppBundle\Entity\User;
use AppBundle\Form\EventType;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Tests\Functional\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use AppBundle\Entity\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;



class EventRestController extends FOSRestController
{

    /**
     * List of all Events
     *
     *
     * @Route("/events", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all events",
     * )
     * @SWG\Tag(name="event")
     */
    public function getAllEvents()
    {
        $events = $this->getDoctrine()->getRepository('AppBundle:Event')->findAll();

        if (count($events) == 0) {
            throw new NotFoundHttpException('Events not found');
        } else {
            $formatted = [];
            foreach ($events as $event) {
                $formatted[] = [
                    'id' => $event->getId(),
                    'name' => $event->getEmail(),
                    'description' => $event->getDescription(),
                ];
            }


        }

        return new JsonResponse($formatted);
    }

    /**
     * Get One Event
     *
     * @Route("/event/{event}", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return event",
     * )
     * @SWG\Tag(name="event")
     */
    public function getEventById(Event $event)
    {
        $event = $this->getDoctrine()->getRepository('AppBundle:Event')->findOneById($event);

        if (count($event) == 0) {
            throw new NotFoundHttpException('Events not found');
        } else {
            $formatted[] = [
                'id' => $event->getId(),
                'name' => $event->getEmail(),
            ];
        }

        return new JsonResponse($formatted);
    }

    /**
     * Get One Event from user
     *
     * @Route("/event/{user}", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return event",
     * )
     * @SWG\Tag(name="event")
     */
    public function getEventByUserId(User $user)
    {
        $event = $this->getDoctrine()->getRepository('AppBundle:Event')->getEventByUserId($user);

        if (count($event) == 0) {
            throw new NotFoundHttpException('Events not found');
        } else {
            $formatted[] = [
                'id' => $event->getId(),
                'name' => $event->getEmail(),
            ];
        }

        return new JsonResponse($formatted);
    }

    /**
     * Add One Event
     *
     * @Route("/event", name="createEvent", methods={"POST"})
     * @SWG\Response(
     *     response=200,
     *     description="Return event",
     * )
     * @SWG\Parameter(
     *     name="Name",
     *     in="query",
     *     type="string",
     *     description="Event name"
     * )
     * @SWG\Parameter(
     *     name="Description",
     *     in="query",
     *     type="string",
     *     description="Event description"
     * )
     * @SWG\Parameter(
     *     name="Date du debut",
     *     in="query",
     *     type="string",
     *     description="Event date format :(AAAA-MM-JJ)"
     * )
     *
     * @SWG\Tag(name="event")
     */
    public function newEvent(Request $request)
    {

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $form->submit($request->request->all()); // Validation des données

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Event bien créée.');

            return $this->redirect($this->generateUrl('event', array('id' => $event->getId())));

        }
        return $this->render('pages/event/createEvent.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    /**
     * Update event
     *
     *
     * @Route("/event/{event}", methods={"PUT"})
     * @SWG\Response(
     *     response=200,
     *     description="Update event",
     * )
     * @SWG\Tag(name="event")
     */
    public function updateInvitation(Event $event){

    }

    /**
     * Delete event
     *
     *
     * @Route("/event", methods={"DELETE"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all event",
     * )
     * @SWG\Tag(name="event")
     */
    public function deleteInvitation(Event $event){

    }

}