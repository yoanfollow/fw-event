<?php


namespace App\Action;


use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\Media;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Action to upload and change user's avatar
 */
final class CreateMediaObjectAction
{
    /** @var ManagerRegistry $managerRegistry */
    private $managerRegistry;

    /** @var UserRepository $userRepository */
    private $userRepository;

    /** @var ValidatorInterface $validator */
    private $validator;

    /** @var ResourceMetadataFactoryInterface $resourceMetadataFactory */
    private $resourceMetadataFactory;

    /**
     * UpdateUserAvatarAction constructor.
     * @param ManagerRegistry $managerRegistry
     * @param UserRepository $userRepository
     * @param ValidatorInterface $validator
     * @param ResourceMetadataFactoryInterface $resourceMetadataFactory
     */
    public function __construct(ManagerRegistry $managerRegistry, UserRepository $userRepository, ValidatorInterface $validator, ResourceMetadataFactoryInterface $resourceMetadataFactory)
    {
        $this->managerRegistry = $managerRegistry;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
    }

    /**
     * @param Request $request
     * @return Media
     */
    public function __invoke(Request $request): Media
    {
        $uploadedFile = $request->files->get('file');

        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }

        $mediaObject = new Media();
        $mediaObject->file = $uploadedFile;

        $this->validator->validate($mediaObject);

        $em = $this->managerRegistry->getManager();
        $em->persist($mediaObject);
        $em->flush();

        return $mediaObject;
    }


}
