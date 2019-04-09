<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 08/04/2019
 * Time: 15:54
 */

namespace AppBundle\Controller\API;


use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Tests\Functional\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;



class UserRestController extends AbstractFOSRestController
{

    /**
     * List of all users
     *
     *
     * @Route("/users", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all users",
     * )
     * @SWG\Tag(name="user")
     */
    public function getAllUsers()
    {
        $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();

        if (count($users) == 0) {
            throw new NotFoundHttpException('Users not found');
        } else {
            $formatted = [];
            foreach ($users as $user) {
                $formatted[] = [
                    'id' => $user->getId(),
                    'name' => $user->getEmail(),
                ];
            }


        }

        return new JsonResponse($formatted);
    }

    /**
     * Get One User
     *
     * @Route("/user/{user}", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return user",
     * )
     * @SWG\Tag(name="user")
     */
    public function getAllUser(User $user)
    {
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneById($user);

        if (count($user) == 0) {
            throw new NotFoundHttpException('Users not found');
        } else {
            $formatted[] = [
                'id' => $user->getId(),
                'name' => $user->getEmail(),
                'pseudo' => $user->getPseudo(),
            ];
        }

        return new JsonResponse($formatted);
    }

    /**
     * Add One User
     *
     * @Route("/user", methods={"POST"})
     * @SWG\Response(
     *     response=200,
     *     description="Return user",
     * )
     * @SWG\Parameter(
     *     name="Email",
     *     in="query",
     *     type="string",
     *     description="User Email"
     * )
     * @SWG\Tag(name="user")
     */
    public function newUser(Request $request)
    {

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all()); // Validation des données

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $user;
        } else {
            return $form;
        }
    }


    /**
     * Update user
     *
     *
     * @Route("/user/{user}", methods={"PUT"})
     * @SWG\Response(
     *     response=200,
     *     description="Update user",
     * )
     * @SWG\Tag(name="user")
     */
    public function updateInvitation(User $user){

    }

    /**
     * Delete user
     *
     *
     * @Route("/user", methods={"DELETE"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all user",
     * )
     * @SWG\Tag(name="user")
     */
    public function deleteInvitation(User $user){

    }




}