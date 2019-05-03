<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\UserRegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthController extends AbstractController
{

    /**
     * Controller for user registration
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return User|Response
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(UserRegistrationType::class, $user);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return new Response('No form submitted', Response::HTTP_BAD_REQUEST);
        }


        if (!$form->isValid()) {
            return new Response(Response::HTTP_BAD_REQUEST);
        }

        // encode the plain password
        $user->setPassword(
            $passwordEncoder->encodePassword(
                $user,
                $form->get('plainPassword')->getData()
            )
        );

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        } catch (\Exception $e) {
            return new Response("Error: ".$e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }


}
