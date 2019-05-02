<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Utilisateur;
use App\Entity\Evenement;
use App\Repository\CommentaireRepository;

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
 * @Route("/commentaire")
 */
class CommentaireController extends AbstractFOSRestController
{

    /**
     * @Rest\Get(
     * path = "/",
     * name = "commentaire_index")
     * @return View
     */
    public function index(CommentaireRepository $commentaireRepository): JsonResponse
    {
        $commentaires =$commentaireRepository->findAll();

        return $this->json($commentaires,Response::HTTP_OK,[],[
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['pseudonyme','password','roles','salt','avatar']

        ]);
    }

    /**
    * @Rest\Post(
    * path = "/{evenementID}/add",
    * name = "commentaire_add")
    * @return JsonResponse
    */
    public function new(ValidatorInterface $validator,Request $request,int $evenementID): JsonResponse
    {
        $utilisateurID=$this->getUser()->getId();
        $utilisateurRepository = $this->getDoctrine()->getRepository(Utilisateur::class);
        $evenementRepository = $this->getDoctrine()->getRepository(Evenement::class);
        $utilisateur = $utilisateurRepository->find($utilisateurID);
        $evenement = $evenementRepository->find($evenementID);
        $commentaire = new Commentaire();        
        $commentaire->setUtilisateur($utilisateur);
        $commentaire->setEvenement($evenement);
        $commentaire->setCommentaire($request->get('commentaire'));
        $commentaire->setNote($request->get('note'));

        $errors=$validator->validate($commentaire);
        if (count($errors)> 0) {
            $errorsString = (string) $errors;
            return $this->json($errorsString, Response::HTTP_BAD_REQUEST);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($commentaire);
        $entityManager->flush();

        return $this->json($commentaire,Response::HTTP_OK,[],[
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['pseudonyme','password','roles','salt','avatar']

        ]);
    }

    /**
     * @Rest\Get(
     * path = "/{id}",
     * name = "commentaire_show")
     * @return JsonResponse
     */
    public function show(CommentaireRepository $commentaireRepository,int $id): JsonResponse
    {
        $commentaire=$commentaireRepository->find($id);

        if(!$commentaire){
            return $this->json(['error' => 'Lieu non touvé'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($commentaire,Response::HTTP_OK,[],[
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['pseudonyme','password','roles','salt','avatar']

        ]);
    }

    /**
     * @Rest\Put(
     * path = "/{id}/edit",
     * name = "commentaire_edit")
     * @return JsonResponse
     */
    public function editCommentaire(CommentaireRepository $commentaireRepository,ValidatorInterface $validator,Request $request,int $id): JsonResponse
    {
        $commentaire = $commentaireRepository->find($id);
        if(!$commentaire){
            return $this->json(['error' => 'Commentaire non touvé'], Response::HTTP_BAD_REQUEST);
        }

        $utilisateurConnecte=$this->getUser();
        $utilisateur=$commentaire->getUtilisateur();

        //Juste le créateur de commentaire à le droit de le modifier
        if($utilisateurConnecte->getID()!= $utilisateur->getId()){
            return $this->json(['error' => 'Vous n\'avez pas le droit de modifier ce commentaire'], Response::HTTP_BAD_REQUEST);
        }

        $commentaire->setCommentaire($request->get('commentaire'));
        $commentaire->setNote($request->get('note'));
        $errors = $validator->validate($commentaire);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return $this->json($errorsString, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->json($commentaire, Response::HTTP_OK);

    }

    /**
    * @Rest\Delete(
    * path = "/{id}/delete",
    * name = "commentaire_delete")
    * @return JsonResponse
    */
    public function delete(CommentaireRepository $commentaireRepository,int $id): JsonResponse
    {
        $commentaire = $commentaireRepository->find($id);
        if(!$commentaire){
            return $this->json(['error' => 'Commentaire non touvé'], Response::HTTP_BAD_REQUEST);
        }

        $utilisateurActuel=$this->getUser();
        $utilisateur=$commentaire->getUtilisateur();

        //Juste le créateur de commentaire à le droit de modifier son evenement
        if($utilisateurActuel->getID()!= $utilisateur->getId()){
            return $this->json(['error' => 'Vous n\'avez pas le droit de modifier ce commentaire'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($commentaire);
        $entityManager->flush();

        return $this->json(['message' => 'le commentaire a été supprimer avec succès'],Response::HTTP_OK);
    }

}
