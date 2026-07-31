<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Folder;
use App\Entity\Quiz;
use App\Entity\QuizAttempt;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

final class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security)
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        $user = $this->security->getUser();
        if (!$user) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        if (QuizAttempt::class === $resourceClass) {
            $queryBuilder->andWhere(sprintf('%s.author = :current_user', $rootAlias))
                ->setParameter('current_user', $user);
        }

        if (Quiz::class === $resourceClass) {
            $queryBuilder->andWhere(sprintf('%s.author = :current_user OR %s.isPublic = true', $rootAlias, $rootAlias))
                ->setParameter('current_user', $user);
        }

        if (Folder::class === $resourceClass) {
            $queryBuilder->andWhere(sprintf(
                '%s.author = :current_user OR %s.isPublic = true OR :current_user MEMBER OF %s.members',
                $rootAlias, $rootAlias, $rootAlias
            ))
                ->setParameter('current_user', $user);
        }
    }
}
