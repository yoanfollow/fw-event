<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\UserRegistrationType;
use App\Helpers\FormHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthController extends AbstractController
{

    /**
     * Controller for user registration
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return User|JsonResponse
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(UserRegistrationType::class, $user);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No form submitted',
            ], Response::HTTP_BAD_REQUEST);
        }


        if (!$form->isValid()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Some fields are missing or invalid',
                'code' => 'invalid_inputs',
                'data' => FormHelper::getErrorMessages($form),
            ], Response::HTTP_BAD_REQUEST);
        }

        // encode the plain password
        $user->setPassword(
            $passwordEncoder->encodePassword(
                $user,
                $form->get('plainPassword')->getData()
            )
        );

        // @todo: Add ApiResponseException to catch exception, render a proper JsonResponse and log error
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();


        return new JsonResponse([
            'success' => true,
        ], Response::HTTP_OK);
    }


}
