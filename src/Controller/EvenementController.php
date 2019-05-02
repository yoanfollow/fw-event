<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\Invitation;
use App\Entity\Utilisateur;
use App\Entity\Lieu;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;

use App\Repository\LieuRepository;
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
 * @Route("/evenement")
 */
class EvenementController extends AbstractFOSRestController
{

    /**Retreive all events
     *
     * @Rest\Get(
     * path = "/",
     * name = "evenement_index")
     * @return JsonResponse
     */
    public function index(EvenementRepository $evenementRepository): JsonResponse
    {
        $evenements =$evenementRepository->findAll();
        return $this->json($evenements,Response::HTTP_OK,[],[
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['pseudonyme','password','roles','salt','avatar']

        ]);
    }

    /** Add an event
     *
    * @Rest\Post(
    * path = "/{id}/add",
    * name = "evenement_add")
    * @return JsonResponse
    */
    public function addEvenement(Request $request ,ValidatorInterface $validator,Utilisateur  $utilisateur): JsonResponse
    {

        $repository = $this->getDoctrine()->getRepository(Lieu::class);

        $lieu = $repository->findOneBy([
            'nom' => $request->get('nomLieu'),
            'numeroDeRue' => $request->get('numeroDeRue'),
            'ville' => $request->get('ville'),
            'codePostale' => $request->get('codePostale'),
            'pays' => $request->get('pays'),
            'rue' => $request->get('rue')
        ]);


        // Si le lieu choisi n’existe pas, on le crée
        if(!$lieu){
            $lieu= new Lieu();
            $lieu->setNom($request->get('nomLieu'));
            $lieu->setNumeroDeRue($request->get('numeroDeRue'));
            $lieu->setVille($request->get('ville'));
            $lieu->setCodePostale($request->get('codePostale'));
            $lieu->setRue($request->get('rue'));
            $lieu->setPays($request->get('pays'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($lieu);
            $entityManager->flush();
        }
        $evenement = new Evenement();
        $evenement->setNom($request->get('nom'));
        $evenement->setDescription($request->get('description'));
        $evenement->setOrganisteur($utilisateur);
        $evenement->setDateDebut(\DateTime::createFromFormat('Y-m-d H:i:s', $request->get('dateDebut')));
        $evenement->setDateFin(\DateTime::createFromFormat('Y-m-d H:i:s', $request->get('dateFin')));
        $evenement->setLieu($lieu);

        $errors=$validator->validate($evenement);
        if (count($errors)) {

            return $this->json($errors,Response::HTTP_BAD_REQUEST);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($evenement);
        $entityManager->flush();

        return $this->json($evenement,Response::HTTP_OK,[],[
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['pseudonyme','password','roles','salt','avatar']

        ]);

    }


    /**
     * @Rest\Get(
     * path = "/{id}",
     * name = "evenement_show")
     * @return JsonResponse
     */
    public function show(EvenementRepository $evenementRepository,int $id): JsonResponse
    {
        $evenement=$evenementRepository->find($id);
        if(!$evenement){
            return $this->json(['error' => 'Lieu non touvé'], Response::HTTP_BAD_REQUEST);
        }
        return $this->json($evenement,Response::HTTP_OK,[],[
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['pseudonyme','password','roles','salt','avatar']

        ]);
    }

    /**
     * @Rest\Put(
     * path = "/{id}/edit",
     * name = "evenement_edit")
     * @return JsonResponse
     */
    public function editEvenement(EvenementRepository $evenementRepository,ValidatorInterface $validator,Request $request,int $id): JsonResponse
    {
        $evenement=$evenementRepository->find($id);
        if(!$evenement){
            return $this->json(['error' => 'Evenement non touvé'], Response::HTTP_BAD_REQUEST);
        }
        $utilisateur=$this->getUser();
        $utilisateurID=$utilisateur->getId();

        //Juste le créateur d'évenement à le droit de modifier son evenement
        $organisateur=$evenement->getOrganisteur();
        if($utilisateurID!=$organisateur->getId()){
            return $this->json(['error' => 'Vous n\'avez pas le droit de modifier cet evenement'], Response::HTTP_BAD_REQUEST);
        }
        $repository = $this->getDoctrine()->getRepository(Lieu::class);

        $lieu = $repository->findOneBy([
            'nom' => $request->get('nomLieu'),
            'numeroDeRue' => $request->get('numeroDeRue'),
            'ville' => $request->get('ville'),
            'codePostale' => $request->get('codePostale'),
            'pays' => $request->get('pays'),
            'rue' => $request->get('rue')
        ]);


        // Si le lieu choisi n’existe pas, on le crée
        if(!$lieu){
            $lieu= new Lieu();
            $lieu->setNom($request->get('nomLieu'));
            $lieu->setNumeroDeRue($request->get('numeroDeRue'));
            $lieu->setVille($request->get('ville'));
            $lieu->setCodePostale($request->get('codePostale'));
            $lieu->setRue($request->get('rue'));
            $lieu->setPays($request->get('pays'));
            $errors=$validator->validate($lieu);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return $this->json($errorsString, Response::HTTP_BAD_REQUEST);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($lieu);
            $entityManager->flush();
        }
        $evenement->setNom($request->get('nom'));
        $evenement->setDescription($request->get('description'));
        $evenement->setOrganisteur($utilisateur);
        $evenement->setDateDebut(\DateTime::createFromFormat('Y-m-d H:i:s', $request->get('dateDebut')));
        $evenement->setDateFin(\DateTime::createFromFormat('Y-m-d H:i:s', $request->get('dateFin')));
        $evenement->setLieu($lieu);

        $errors=$validator->validate($evenement);
        if (count($errors)) {
            $errorsString = (string) $errors;
            return $this->json($errorsString,Response::HTTP_BAD_REQUEST);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($evenement);
        $entityManager->flush();

        return $this->json($evenement,Response::HTTP_OK,[],[
            ObjectNormalizer::IGNORED_ATTRIBUTES => ['pseudonyme','password','roles','salt','avatar']

        ]);

    }

    /**
     * @Rest\Delete(
     * path = "/{id}/delete",
     * name = "evenement_delete")
     * @return View
     */
    public function deleteEvenement(EvenementRepository $evenementRepository,int $id): View
    {
        $evenement=$evenementRepository->find($id);
        if(!$evenement){
            return View::create(['error' => 'Evenement non touvé'], Response::HTTP_BAD_REQUEST);
        }
        $user=$this->getUser();
        $idUser=$user->getId();

        //Juste le créateur d'évenement à le droit de supprimer son evenement
        $organisateur=$evenement->getOrganisteur();
        if($idUser!=$organisateur->getId()){
            return View::create(['error' => 'Vous n\'avez pas le droit de supprimer cet evenement'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($evenement);
        $entityManager->flush();

        return  View::create(['message' => 'L\'évenement a été supprimer avec succès'], Response::HTTP_OK);
    }

    /**
     * @Rest\Get(
     * path = "/{evenementID}/invitesConfirmes",
     * name = "evenements_invites_confirmes")
     * @return View
     */
    public function evenementInvitesConfirmes(int $evenementID): View
    {
        $entityManager = $this->getDoctrine()->getManager();;
        $query = $entityManager->createQuery(
            'SELECT  U.id , U.pseudonyme, U.email   
                FROM App\Entity\Invitation I , App\Entity\Utilisateur U
                WHERE I.evenement=:evenement AND I.destinataire=U.id AND I.confirmation=true'
        )->setParameter('evenement',$evenementID);

        $invites = $query->execute();

        if(!$invites){
            return View::create(['error' => 'Aucun invités pour cet evenement'], Response::HTTP_BAD_REQUEST);
        }

        return  View::create($invites, Response::HTTP_OK);
    }

    /**
     * @Rest\Get(
     * path = "/{evenementID}/invitesNonConfirmes",
     * name = "evenements_invites_non_confirmes")
     * @return View
     */
    public function evenementInvitesNonConfirmes(int $evenementID): View
    {
        $entityManager = $this->getDoctrine()->getManager();;
        $query = $entityManager->createQuery(
            'SELECT  U.id , U.pseudonyme, U.email   
                FROM App\Entity\Invitation I , App\Entity\Utilisateur U
                WHERE I.evenement=:evenement AND I.destinataire=U.id AND I.confirmation=false'
        )->setParameter('evenement',$evenementID);

        $invites = $query->execute();

        if(!$invites){
            return View::create(['error' => 'Aucun invités pour cet evenement'], Response::HTTP_BAD_REQUEST);
        }

        return  View::create($invites, Response::HTTP_OK);
    }

    /**
     * @Rest\Get(
     * path = "/{evenementID}/invites",
     * name = "evenements_invites")
     * @return View
     */
    public function evenementInvites(int $evenementID): View
    {
        $entityManager = $this->getDoctrine()->getManager();;
        $query = $entityManager->createQuery(
            'SELECT  U.id , U.pseudonyme, U.email   
                FROM App\Entity\Invitation I , App\Entity\Utilisateur U
                WHERE I.evenement=:evenement AND I.destinataire=U.id'
        )->setParameter('evenement',$evenementID);

        $invites = $query->execute();

        if(!$invites){
            return View::create(['error' => 'Aucun invités pour cet evenement'], Response::HTTP_BAD_REQUEST);
        }

        return  View::create($invites, Response::HTTP_OK);
    }


}
