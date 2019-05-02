<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\Utilisateur;
use App\Entity\Invitation;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View ;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/utilisateur")
 */
class UtilisateurController extends AbstractFOSRestController
{
    /**
     * @Rest\Get(
     * path = "/",
     * name = "utilisateur_index")
     * @return JsonResponse
     */
    public function index(UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        $utilisateurs=$utilisateurRepository->findAll();
        //die(var_dump($utilisateurs));
        return $this->json($utilisateurs,Response::HTTP_OK,[],[
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['pseudonyme','password','salt']

        ]);
    }

    /**
    * @Rest\Post(
    * path = "/add",
    * name = "utilisateur_add")
    * @return JsonResponse
    */
    public function addUtilisateur(Request $request,ContainerInterface $container,UserPasswordEncoderInterface $encoder,ValidatorInterface $validator): JsonResponse
    {
        $utilisateur = new Utilisateur();
        $encoded = $encoder->encodePassword($utilisateur, $request->get('password'));
        $utilisateur->setPassword($encoded);
        $utilisateur->setRoles(array('ROLE_USER'));
        $utilisateur->setPseudonyme($request->get('pseudonyme'));
        $utilisateur->setEmail($request->get('email'));
        $file=$request->files->get('avatar');

        if($file){
            $fileName=md5(uniqid()).'.'.$file->guessClientExtension();
            $file->move($container->getParameter('kernel.project_dir').'/public/upload_directory',$fileName);
            $utilisateur->setAvatar($container->getParameter('kernel.project_dir').'/public/upload_directory/'.$fileName);
        }

        $errors=$validator->validate($utilisateur);
        if (count($errors)>0) {
            $errorsString = (string) $errors;
            return $this->json($errorsString, Response::HTTP_BAD_REQUEST);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($utilisateur);
        $entityManager->flush();


        return $this->json($utilisateur,Response::HTTP_CREATED,[],[
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['pseudonyme','password','salt']

        ]);
    }

    /**
     * @Rest\Get(
     * path = "/{id}",
     * name = "utilisateur_show")
     * @return JsonResponse
     */
    public function show(UtilisateurRepository $utilisateurRepository,int $id): JsonResponse
    {
        $utilisateur=$utilisateurRepository->find($id);
        if(!$utilisateur){
            return $this->json(['error' => 'Utilisateur non touvé'], Response::HTTP_BAD_REQUEST);
        }
        return $this->json($utilisateur,Response::HTTP_OK,[],[
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['pseudonyme','password','salt']

        ]);
    }


    /**
     * @Rest\Post(
     * path = "/{utilisateurID}/edit",
     * name = "utilisateur_edit")
     * @return JsonResponse
     */
    public function editUtilisateur(Request $request,ContainerInterface $container,UserPasswordEncoderInterface $encoder,int $utilisateurID,ValidatorInterface $validator): JsonResponse
    {
        $utilisateur=$this->getUser();
        if($utilisateurID!=$utilisateur->getId()){
            return $this->Json(['error' => 'Vous n\'avez pas le droit de modifier cet utilisateur'], Response::HTTP_BAD_REQUEST);
        }
        $encoded = $encoder->encodePassword($utilisateur, $request->get('password'));
        $utilisateur->setPassword($encoded);
        $utilisateur->setPseudonyme($request->get('pseudonyme'));
        $utilisateur->setEmail($request->get('email'));
        $file=$request->files->get('avatar');

        if($file){
            $avatarPath=$container->getParameter('kernel.project_dir').'/public/upload_directory/'.$utilisateur->getAvatar();
            if($avatarPath) {
                unlink($avatarPath);
            }
            $fileName=md5(uniqid()).'.'.$file->guessClientExtension();
            $file->move($container->getParameter('kernel.project_dir').'/public/upload_directory',$fileName);
            $utilisateur->setAvatar($container->getParameter('kernel.project_dir').'/public/upload_directory'.$fileName);
        }

        $errors=$validator->validate($utilisateur);
        if (count($errors)) {
            $errorsString = (string) $errors;
            return $this->json($errorsString, Response::HTTP_BAD_REQUEST);
        }
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->json($utilisateur,Response::HTTP_OK,[],[
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['pseudonyme','password','salt']

        ]);
    }

    /**
    * @Rest\Delete(
    * path = "/{id}/delete",
    * name = "delete_utilisateur")
    * @return View
    */
    public function deleteUtilisateur(UtilisateurRepository $utilisateurRepository,int $id): View
    {
        $utilisateurActuel=$this->getUser();
        if($id!=$utilisateurActuel->getId()){
            return View::create(['error' => 'Vous n\'avez pas le droit de supprimer cet utilisateur'], Response::HTTP_BAD_REQUEST);
        }
        $utilisateur=$utilisateurRepository->find($id);
        $avatar = $utilisateur->getAvatar();
        if($avatar){
            unlink($avatar);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($utilisateur);
        $entityManager->flush();

        return  View::create(['message' => 'L utilisateur a été supprimer avec succès'], Response::HTTP_OK);
    }

    /**
    * @Rest\Get(
    * path = "/evenements/invitations",
    * name = "utilisateur_invitation")
    * @return View
    */
    public function utilisateurInvitations(): View
    {
        $utilisateurID=$this->getUser()->getId();
        $invitationRepository = $this->getDoctrine()->getRepository(Invitation::class);
        $invitations = $invitationRepository->findBy(['destinataire' => $utilisateurID]);

        return  View::create($invitations, Response::HTTP_OK);
    }

    /**
     * @Rest\Get(
     * path = "/evenements/organises",
     * name = "utilisateur_evenements")
     * @return View
     */
    public function utiliateurEvenements(): View
    {
        $utilisateurID=$this->getUser()->getId();

        $evenementRepository = $this->getDoctrine()->getRepository(Evenement::class);
        $evenements = $evenementRepository->findBy(['organisteur' => $utilisateurID]);

        return  View::create($evenements, Response::HTTP_OK);
    }

    /**
     * @Rest\Get(
     * path = "/evenements/participations",
     * name = "utilisateur_participation")
     * @return View
     */
    public function utilisateurParticipations(): View
    {
        $utilisateurID=$this->getUser()->getId();
        $entityManager = $this->getDoctrine()->getManager();;
        $query = $entityManager->createQuery(
                'SELECT E.nom ,E.description, E.dateDebut, E.dateFin  
                FROM App\Entity\Evenement E , App\Entity\Invitation I
                WHERE I.evenement = E.id AND I.confirmation = true AND I.destinataire = :utilisateur'
        )->setParameter('utilisateur',$utilisateurID);

        $evenements = $query->execute();

        return  View::create($evenements, Response::HTTP_OK);
    }
}
