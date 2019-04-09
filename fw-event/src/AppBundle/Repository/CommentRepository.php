<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 08/04/2019
 * Time: 23:19
 */

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class CommentRepository extends EntityRepository
{

    public function getCommentByUserId(User $user) {

        $query = $this->getEntityManager()
            ->createQuery(
                '* FROM comment  '.
                'WHERE user_id = :user'
            )->setParameter('user', $user->getId());

        return $query->getResult();
    }
}