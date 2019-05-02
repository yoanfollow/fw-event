<?php

namespace App\Controller;

use App\Entity\Invitation;
use App\Entity\Evenement;
use App\Entity\Utilisateur;
use App\Form\InvitationType;
use App\Repository\EvenementRepository;
use App\Repository\InvitationRepository;

use App\Repository\LieuRepository;
use App\Repository\UtilisateurRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View ;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * @Route("/invitation")
 */
class InvitationController extends AbstractFOSRestController
{

    /**
     * @Rest\Get(
     * path = "/",
     * name = "invitation_index")
     * @return View
     */
    public function index(InvitationRepository $invitationRepository): View
    {
        $invitations =$invitationRepository->findAll();
        return View::create($invitations, Response::HTTP_OK);
    }

    /**
    * @Rest\Post(
    * path = "/{id}/add",
    * name = "invitation_add")
    * @return JsonResponse
    */
    public function addInvitation(Request $request ,ValidatorInterface $validator,Evenement  $evenement): JsonResponse
    {
        $utilisateurRepository = $this->getDoctrine()->getRepository(Utilisateur::class);
        $destinataire = $utilisateurRepository->findOneBy(['email' => $request->get('email')]);

        if(!$destinataire){
            return $this->json(['error' => 'Destinataire non touvé'], Response::HTTP_BAD_REQUEST);
        }

        $invitation = new Invitation();        
        $invitation->setconfirmation(false);
        $dateLimite=$request->get('dateLimit');

        if($dateLimite){
            $invitation->setDateLimit(\DateTime::createFromFormat('Y-m-d H:i:s',$dateLimite));
        }

        $invitation->setEvenement($evenement);
        $invitation->setDestinataire($destinataire);
        $errors=$validator->validate($invitation);
        //die(var_dump($errors));
        if (count($errors)){
            $errorsString = (string) $errors;
            return $this->json($errorsString, Response::HTTP_BAD_REQUEST);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($invitation);
        $entityManager->flush();

        return $this->json($invitation,Response::HTTP_CREATED,[],[
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['pseudonyme','avatar','password','salt','roles']

        ]);
    }


    /**
     * @Rest\Get(
     * path = "/{id}",
     * name = "invitation_show")
     * @return JsonResponse
     */
    public function show(InvitationRepository $invitationRepository,int $id): JsonResponse
    {
        $invitation=$invitationRepository->find($id);

        if(!$invitation){
            return $this->json(['error' => 'Lieu non touvé'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($invitation,Response::HTTP_CREATED,[],[
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['pseudonyme','password','avatar','salt','roles']

        ]);
    }

    /**
     * @Rest\Get(
     * path = "/{id}",
     * name = "invitation_edit")
     * @return JsonResponse
     */
    public function editInvitation(Request $request,ValidatorInterface $validator,InvitationRepository $invitationRepository,int $id): JsonResponse
    {
        $invitation = $invitationRepository->find($id);
        if(!$invitation){
            return View::create(['error' => 'Invitation non touvé'], Response::HTTP_BAD_REQUEST);
        }
        $uilisateurID=$this->getUser()->getId();
        $evenement=$invitation->getEvenement();
        if($uilisateurID != $evenement->getOrganisteur()->getId()){
            return $this->json(['error' => 'Vous n\'avez pas le droit de modifier cette invitation'], Response::HTTP_BAD_REQUEST);
        }
        $utilisateurRepository = $this->getDoctrine()->getRepository(Utilisateur::class);
        $destinataire = $utilisateurRepository->findOneBy(['email' => $request->get('email')]);

        if(!$destinataire){
            return $this->json(['error' => 'Destinataire non touvé'], Response::HTTP_BAD_REQUEST);
        }

        $invitation->setconfirmation(false);
        $dateLimite=$request->get('dateLimit');

        if($dateLimite){
            $invitation->setDateLimit(\DateTime::createFromFormat('Y-m-d H:i:s',$dateLimite));
        }

        $invitation->setDestinataire($destinataire);
        $errors=$validator->validate($invitation);
        //die(var_dump($errors));
        if (count($errors)){
            $errorsString = (string) $errors;
            return $this->json($errorsString, Response::HTTP_BAD_REQUEST);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();
        return $this->json($invitation,Response::HTTP_CREATED,[],[
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['pseudonyme','avatar','password','salt','roles']

        ]);
    }


    /**
     * @Rest\Delete(
     * path = "/{id}/delete",
     * name = "invitation_delete")
     * @return JsonResponse
     */
    public function delete(InvitationRepository $invitationRepository,int $id): JsonResponse
    {
        $invitation = $invitationRepository->find($id);
        if(!$invitation){
            return View::create(['error' => 'Invitation non touvé'], Response::HTTP_BAD_REQUEST);
        }
        $uilisateurID=$this->getUser()->getId();
        $evenement=$invitation->getEvenement();
        if($uilisateurID != $evenement->getOrganisteur()->getId()){
            return $this->json(['error' => 'Vous n\'avez pas le droit de supprimer cette invitation'], Response::HTTP_BAD_REQUEST);
        }


        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($invitation);
        $entityManager->flush();

        return  $this->json(['message' => 'L \'invitation a été supprimer avec succès'], Response::HTTP_OK);
    }

    /**
     * @Rest\Get(
     * path = "/{invitationID}/confirmation",
     * name = "invitation_confirmation")
     * @return View
     */
    public function confirmationInvitation(EvenementRepository $evenementRepository,InvitationRepository $invitationRepository,int $invitationID): View
    {
        $distinataire = $this->getUser();
        $invitation = $invitationRepository->find($invitationID);
        if(!$invitation)
        {
            return View::create(['error' => 'Invitation non touvé'], Response::HTTP_BAD_REQUEST);
        }

        $evenement = $evenementRepository->find($invitation->getEvenement());

        if($invitation->getDestinataire() <=> $distinataire)
        {
            return View::create(['error' => 'vous n\'êtes pas invités à cet évenement '], Response::HTTP_BAD_REQUEST);
        }

        $dateActuel = new \DateTime('now');
        if($invitation->getDateLimit())
        {
            if($dateActuel > $invitation->getDateLimit())
            {
                return View::create(['error' => 'L\'invitation a expiré'], Response::HTTP_BAD_REQUEST);
            }

        }
        if(!$invitation->getDateLimit())
        {
            if($dateActuel > $evenement->getDateDebut())
            {
                return View::create(['error' => 'L\'invitation a expiré'], Response::HTTP_BAD_REQUEST);
            }

        }

        $invitation->setConfirmation(true);
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return View::create($invitation, Response::HTTP_OK);

    }

}
