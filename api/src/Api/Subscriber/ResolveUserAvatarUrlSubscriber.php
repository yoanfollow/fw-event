<?php


namespace App\Api\Subscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Util\RequestAttributesExtractor;
use App\Entity\Media;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Event subscriber to resolve Avatar URL on user normalization
 */
final class ResolveUserAvatarUrlSubscriber implements EventSubscriberInterface
{
    /** @var StorageInterface $storage */
    private $storage;

    /**
     * ResolveMediaObjectContentUrlSubscriber constructor.
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onPreSerialize', EventPriorities::PRE_SERIALIZE],
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onPreSerialize(GetResponseForControllerResultEvent $event): void
    {
        $controllerResult = $event->getControllerResult();
        $request = $event->getRequest();

        if ($controllerResult instanceof Response || !$request->attributes->getBoolean('_api_respond', true)) {
            return;
        }

        $attributes = RequestAttributesExtractor::extractAttributes($request);
        if (!$attributes || !\is_a($attributes['resource_class'], User::class, true)) {
            return;
        }

        // Get all user entities and set contenturl
        $users = $controllerResult;

        if (!is_iterable($users)) {
            $users = [$users];
        }

        foreach ($users as $user) {
            if (!$user instanceof User || !$user->getAvatar()) {
                continue;
            }

            $user->setAvatarUrl($this->storage->resolveUri($user->getAvatar(), 'file'));
        }
    }
}
