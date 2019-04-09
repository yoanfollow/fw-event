<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 08/04/2019
 * Time: 20:35
 */

namespace AppBundle\Controller\API;




use AppBundle\Entity\Comment;
use AppBundle\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

class CommentRestController extends AbstractFOSRestController
{

    /**
     * List of all comment
     *
     *
     * @Route("/comments", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all comment",
     * )
     * @SWG\Tag(name="comment")
     */
    public function getAllComments(){
        $comments = $this->getDoctrine()->getRepository('AppBundle:Comment')->findAll();

        if (count($comments) == 0) {
            throw new NotFoundHttpException('Comments not found');
        } else {
            $formatted = [];
            foreach ($comments as $comment) {
                $formatted[] = [
                    'id' => $comment->getId(),
                    'comment' => $comment->getComment(),
                    'user' => $comment->getCommentUser(),
                ];
            }


        }

        return new JsonResponse($formatted);
    }

    /**
     * Get one comment
     *
     *
     * @Route("/comment/{comment}", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all comment",
     * )
     * @SWG\Tag(name="comment")
     */
    public function getCommentById(Comment $comment){
        $comment = $this->getDoctrine()->getRepository('AppBundle:Comment')->findOneById($comment);

        if (count($comment) == 0) {
            throw new NotFoundHttpException('Comments not found');
        } else {
            $formatted[] = [
                'id' => $comment->getId(),
                'comment' => $comment->getComment(),
                'user' => $comment->getCommentUser(),
            ];
        }

        return new JsonResponse($formatted);
    }

    /**
     * Get One Event from user
     *
     * @Route("/comment/{user}", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Return event",
     * )
     * @SWG\Tag(name="comment")
     */
    public function getCommentByUserId(User $user)
    {
        $comment = $this->getDoctrine()->getRepository('AppBundle:Comment')->getCommentByUserId($user);

        if (count($comment) == 0) {
            throw new NotFoundHttpException('Events not found');
        } else {
            $formatted[] = [
                'id' => $comment->getId(),
                'comment' => $comment->getComment(),
                'user' => $comment->getCommentUser(),
            ];
        }

        return new JsonResponse($formatted);
    }

    /**
     * create comment
     *
     *
     * @Route("/comment", methods={"POST"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all comment",
     * )
     * @SWG\Tag(name="comment")
     */
    public function newComment(){

    }

    /**
     * Update comment
     *
     *
     * @Route("/comment/{comment}", methods={"PUT"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all comment",
     * )
     * @SWG\Tag(name="comment")
     */
    public function updateComment(Comment $comment){

    }

    /**
     * Delete comment
     *
     *
     * @Route("/comment", methods={"DELETE"})
     * @SWG\Response(
     *     response=200,
     *     description="Return all events",
     * )
     * @SWG\Tag(name="comment")
     */
    public function deleteComment(Comment $comment){

    }


}