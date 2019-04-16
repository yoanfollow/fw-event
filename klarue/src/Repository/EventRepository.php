<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return Event[] Returns an array of Event objects
     */
    public function findMyEvents($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.organisator = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
        ;
    }

   /**
     * @return Events[] Returns an array of Event objects
     */
    public function findMyParticipateEvents($value)
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.participants','i')
            ->where("i.to_user = :user")
            ->andWhere("i.is_confirmed = true")
            ->setParameter("user",$value)
            ->getQuery()
            ->getResult();

    }


    /*
    public function findOneBySomeField($value): ?Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
