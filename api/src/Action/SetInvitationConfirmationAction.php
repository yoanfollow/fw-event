<?php


namespace App\Action;


use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use App\Entity\Invitation;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SetInvitationConfirmationAction
{
    /** @var ManagerRegistry $managerRegistry */
    private $managerRegistry;

    /** @var ResourceMetadataFactoryInterface $resourceMetadataFactory */
    private $resourceMetadataFactory;

    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface $authorizationChecker */
    private $authorizationChecker;


    public function __construct(
        ManagerRegistry $managerRegistry,
        ResourceMetadataFactoryInterface $resourceMetadataFactory,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param Invitation $data
     * @param Request $request
     * @return Invitation
     * @throws \Exception
     */
    public function __invoke(Invitation $data, Request $request)
    {
        $confirmed = $request->request->getBoolean('confirmed');

        if (empty($data->getRecipient())) {
            throw new \Exception('Recipient is empty');
        }

        $currentUser = $this->tokenStorage->getToken()->getUser();
        if ($data->getRecipient()->getId() !== $currentUser->getId() && !$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException(sprintf('Access denied'));
        }

        $data->setConfirmed($confirmed);

        $em = $this->managerRegistry->getManager();
        $em->flush();

        return $data;
    }

}
