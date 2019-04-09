<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 08/04/2019
 * Time: 20:35
 */

namespace AppBundle\Controller\API;




use AppBundle\Entity\Comment;
use AppBundle\Entity\Place;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

class PlaceRestController extends AbstractFOSRestController
{

    /**
     * List of all place
     *
     *
     * @Route("/places", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all place",
     * )
     * @SWG\Tag(name="place")
     */
    public function getAllPlaces(){

    }

    /**
     * Get one place
     *
     *
     * @Route("/place/{place}", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all place",
     * )
     * @SWG\Tag(name="place")
     */
    public function getPlace(Place $place){

    }

    /**
     * create place
     *
     *
     * @Route("/place", methods={"POST"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all place",
     * )
     * @SWG\Tag(name="place")
     */
    public function newPlace(){

    }

    /**
     * Update place
     *
     *
     * @Route("/place/{place}", methods={"PUT"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all place",
     * )
     * @SWG\Tag(name="place")
     */
    public function updatePlace(Place $place){

    }

    /**
     * Delete place
     *
     *
     * @Route("/place", methods={"DELETE"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all events",
     * )
     * @SWG\Tag(name="place")
     */
    public function deletePlace(Place $place){

    }


}