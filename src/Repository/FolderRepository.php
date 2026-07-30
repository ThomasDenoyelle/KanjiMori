<?php

namespace App\Repository;

use App\Entity\Folder;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Folder>
 */
class FolderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Folder::class);
    }

    public function findAllJoindedClassByUser(User $user)
    {
        return $this->createQueryBuilder('f')
            ->innerJoin('f.members', 'm')
            ->andWhere('m.id = :user')
            ->andWhere('f.isPublic = true')
            ->setParameter('user', $user->getId())
            ->leftJoin('f.quizzes', 'q')
            ->addSelect('q')

            ->leftJoin('f.members', 'all_members')
            ->addSelect('all_members')

            ->leftJoin('f.author', 'a')
            ->addSelect('a')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllFolderByUser(User $user)
    {
        return $this->createQueryBuilder('f')
            ->select('f, q, m')
            ->leftJoin('f.quizzes', 'q')
            ->leftJoin('f.members', 'm')
            ->andWhere('f.author = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findFolderWithEverything(Folder $folder): ?Folder
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.author', 'a')
            ->addSelect('a')
            ->leftJoin('f.members', 'm')
            ->addSelect('m')
            ->leftJoin('f.quizzes', 'q')
            ->addSelect('q')
            ->leftJoin('q.questions', 'quest')
            ->addSelect('quest')
            ->where('f.id = :id')
            ->setParameter('id', $folder->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
