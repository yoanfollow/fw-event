<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


class AuthenticationSuccessListener {

    /** @var NormalizerInterface $serializer */
    private $serializer;

    /**
     * AuthenticationSuccessListener constructor.
     * @param NormalizerInterface $serializer
     */
    public function __construct(NormalizerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }
        $userInfoResponse= $this->serializer->normalize($user,'jsonld');
        $data['user'] = $userInfoResponse;

        $event->setData($data);
    }
}
