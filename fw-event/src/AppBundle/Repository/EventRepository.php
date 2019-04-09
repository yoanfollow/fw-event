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

class EventRepository extends EntityRepository
{

    public function getEventByUserId(User $user) {

        $query = $this->getEntityManager()
            ->createQuery(
                '* FROM event  '.
                'WHERE creator_user_id = :user'
            )->setParameter('user', $user->getId());

        return $query->getResult();
    }
}