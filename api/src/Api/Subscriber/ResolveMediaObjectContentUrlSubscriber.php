<?php


namespace App\Api\Subscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Util\RequestAttributesExtractor;
use App\Entity\Media;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Vich\UploaderBundle\Storage\StorageInterface;


/**
 * Event subscriber to resolve Avatar URL on any media normalization
 */
final class ResolveMediaObjectContentUrlSubscriber implements EventSubscriberInterface
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

        // If not a Media request from API
        $attributes = RequestAttributesExtractor::extractAttributes($request);
        if (!$attributes || !\is_a($attributes['resource_class'], Media::class, true)) {
            return;
        }

        // Get all media entities and set contenturl
        $medias = $controllerResult;

        if (!is_iterable($medias)) {
            $medias = [$medias];
        }

        foreach ($medias as $media) {
            if (!$media instanceof Media) {
                continue;
            }

            $media->contentUrl = $this->storage->resolveUri($media, 'file');
        }
    }
}
