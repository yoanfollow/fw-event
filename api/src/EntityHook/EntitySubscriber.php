<?php


namespace App\EntityHook;


use App\Entity\Comment;
use App\Entity\Event;
use App\Entity\User;
use App\Helpers\DateHelper;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

class EntitySubscriber implements EventSubscriber
{

    /** @var Security $security */
    private $security;

    /**
     * EntitySubscriber constructor.
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // Automatically set createdAt in entity implementing AutoCreatedAtInterface (if it's not filled)
        if ($entity instanceof AutoCreatedAtInterface && empty($entity->getCreatedAt())) {
            $entity->setCreatedAt(DateHelper::getToday(DateHelper::UTC_PARIS_TZ));
        }

        // Automatically set author in comment if it's not filled
        if ($entity instanceof Comment && empty($entity->getAuthor())) {
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $entity->setAuthor($user);
            }
        }

        // Automatically set organizer in event if it's not filled
        if ($entity instanceof Event && empty($entity->getOrganizer())) {
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $entity->setOrganizer($user);
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // Automatically set createdAt in entity implementing AutoUpdatedAtInterface (if it's not filled)
        if ($entity instanceof AutoUpdatedAtInterface) {
            $entity->setUpdatedAt(DateHelper::getToday(DateHelper::UTC_PARIS_TZ));
        }
    }

    /**
     * @inheritdoc
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'postUpdate',
        ];
    }
}
