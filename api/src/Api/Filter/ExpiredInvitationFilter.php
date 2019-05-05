<?php


namespace App\Api\Filter;


use ApiPlatform\Core\Bridge\Doctrine\Common\Filter\BooleanFilterTrait;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Invitation;
use Doctrine\ORM\QueryBuilder;

final class ExpiredInvitationFilter extends AbstractContextAwareFilter
{

    use BooleanFilterTrait;

    /**
     * @param string $property
     * @param $value
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param string|null $operationName
     */
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {

        if ($property !== 'isExpired' || $resourceClass !== Invitation::class) {
            return;
        }

        [$eventAlias, $eventProperty, $associations] = $this->addJoinsForNestedProperty(
            'event.endAt',
            $queryBuilder->getRootAliases()[0],
            $queryBuilder,
            $queryNameGenerator,
            $resourceClass
        );


        $value = $this->normalizeValue($value, $property);
        if ($value) {
            $queryString = '(o.expireAt IS NOT NULL AND o.expireAt < :today) AND '.$eventAlias.'.'.$eventProperty.' < :today';
        } else {
            $queryString = '(o.expireAt IS NOT NULL AND o.expireAt >= :today) AND '.$eventAlias.'.'.$eventProperty.' >= :today';
        }
        $queryBuilder
            ->leftJoin('o.event', 'event')
            ->andWhere($queryString)
            ->setParameter('today', new \DateTime());
    }

    /**
     * This function is only used to hook in documentation generators (supported by Swagger and Hydra)
     * @param string $resourceClass
     * @return array
     */
    public function getDescription(string $resourceClass): array
    {
        $description["isExpired"] = [
            'property' => 'isExpired',
            'type' => 'boolean',
            'required' => false,
            'swagger' => [
                'description' => 'Filter expired invitation based on the expiration date or the end of the event',
                'name' => 'isExpired',
                'type' => 'boolean',
            ],
        ];

        return $description;
    }
}
