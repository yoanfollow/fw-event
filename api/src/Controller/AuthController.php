<?php


namespace App\Controller;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthController extends AbstractController
{

    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $em = $this->getDoctrine()->getManager();

        $username = $request->request->get('_username');
        $password = $request->request->get('_password');

        if (!$username || !$password) {
            return new Response("Missing credentials", Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = new User();
            $user
                ->setUsername($username)
                ->setPassword($encoder->encodePassword($user, $password))
                ->setEmail('jeremie.quinson@gmail.com')
            ;
            $em->persist($user);
            $em->flush();
        } catch (\Exception $e) {
            return new Response("Error: ".$e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(sprintf('User %s successfully created', $user->getUsername()));
    }


}
