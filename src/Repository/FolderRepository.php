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
            ->getQuery()
            ->getResult()
        ;
    }
}
