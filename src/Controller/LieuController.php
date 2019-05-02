<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View ;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/lieu")
 */
class LieuController extends AbstractFOSRestController
{
    /**
     * @Rest\Get(
     * path = "/",
     * name = "lieu_index")
     * @return View
     */
    public function index(LieuRepository $lieuRepository): View
    {
        $lieux=$lieuRepository->findAll();
        if(!$lieux){
            return View::create(['message' => 'Aucun lieu trouvé'],Response::HTTP_OK);
        }
        return View::create($lieux, Response::HTTP_OK);
    }

    /**
    * @Rest\Post(
    * path = "/add",
    * name = "lieu_add")
    * @return View
    */
    public function addLieu(Request $request,ValidatorInterface $validator): View
    {
        $lieu = new Lieu();        
        $lieu->setNom($request->get('nom'));
        $lieu->setNumeroDeRue($request->get('numeroDeRue'));
        $lieu->setVille($request->get('ville'));
        $lieu->setCodePostale($request->get('codePostale'));
        $lieu->setRue($request->get('rue'));
        $lieu->setPays($request->get('pays'));
        $errors=$validator->validate($lieu);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
           return View::create($errorsString, Response::HTTP_BAD_REQUEST);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($lieu);
        $entityManager->flush();
        return View::create($lieu, Response::HTTP_CREATED);
    }

    /**
     * @Rest\Put(
     * path = "/{id}/edit",
     * name = "lieu_edit")
     */
    public function editLieu(Request $request,ValidatorInterface $validator,Lieu $lieu): View
    {

        $body = $request->getContent();
        $data = json_decode($body, true);
        $form = $this->createForm(LieuType::class, $lieu);
        $form->submit($data);
        $errors = $validator->validate($lieu);
        if (count($errors) > 0){
            $errorsString = (string) $errors;
            return $this->view($errorsString, Response::HTTP_BAD_REQUEST);
        }
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return View::create($lieu, Response::HTTP_OK);
    }

    /**
    * @Rest\Delete(
    * path = "/{id}/delete",
    * name = "lieu_delete")
    * @return View
    */
    public function deleteLieu(LieuRepository $lieuRepository,int $id): View
    {
        $lieu=$lieuRepository->find($id);

        if(!$lieu){
            return View::create(['error' => 'Lieu non touvé'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($lieu);
        $entityManager->flush();

        return  View::create(['message' => 'Le lieu a été supprimer avec succès'], Response::HTTP_OK);
    }


    /**
     * @Rest\Get(
     * path = "/{id}",
     * name = "lieu_show")
     * @return View
     */
    public function show(LieuRepository $lieuRepository,int $id): View
    {
        $lieu=$lieuRepository->find($id);
        if(!$lieu){
            return View::create(['error' => 'Lieu non touvé'], Response::HTTP_BAD_REQUEST);
        }
        return View::create($lieu, Response::HTTP_OK);
    }

}
