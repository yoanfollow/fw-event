<?php


namespace App\EntityHook;


use App\Helpers\DateHelper;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

class EntitySubscriber implements EventSubscriber
{

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // Add created at
        if ($entity instanceof AutoCreatedAtInterface) {
            $entity->setCreatedAt(DateHelper::getToday(DateHelper::UTC_PARIS_TZ));
        }
        // Updated at
        if ($entity instanceof AutoUpdatedAtInterface) {
            $entity->setUpdatedAt(DateHelper::getToday(DateHelper::UTC_PARIS_TZ));
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // Updated at
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
