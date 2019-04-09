<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 08/04/2019
 * Time: 20:35
 */

namespace AppBundle\Controller\API;




use AppBundle\Entity\Invitation;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

class InvitationRestController extends AbstractFOSRestController
{

    /**
     * List of all invitation
     *
     *
     * @Route("/invitations", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all invitation",
     * )
     * @SWG\Tag(name="invitation")
     */
    public function getAllInvitations(){

    }

    /**
     * Get one invitation
     *
     *
     * @Route("/invitation/{invitation}", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all invitation",
     * )
     * @SWG\Tag(name="invitation")
     */
    public function getInvitation(Invitation $invitation){

    }

    /**
     * create invitation
     *
     *
     * @Route("/invitation", methods={"POST"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all invitation",
     * )
     * @SWG\Tag(name="invitation")
     */
    public function newInvitation(){

    }

    /**
     * Update invitation
     *
     *
     * @Route("/invitation/{invitation}", methods={"PUT"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all invitation",
     * )
     * @SWG\Tag(name="invitation")
     */
    public function updateInvitation(Invitation $invitation){

    }

    /**
     * Delete invitation
     *
     *
     * @Route("/invitation", methods={"DELETE"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all events",
     * )
     * @SWG\Tag(name="invitation")
     */
    public function deleteInvitation(Invitation $invitation){

    }


}