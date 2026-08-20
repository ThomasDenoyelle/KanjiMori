<?php

namespace App\Repository;

use App\Entity\QuizAttempt;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuizAttempt>
 */
class QuizAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizAttempt::class);
    }

    public function findAllQuizAttemptByUser(User $user): array
    {
        return $this->createQueryBuilder('quizAttempt')
            ->leftJoin('quizAttempt.answerAttempts', 'answerAttempts')
            ->leftJoin('quizAttempt.quiz', 'quiz')
            ->select('quizAttempt, answerAttempts, quiz')
            ->where('quizAttempt.author = :user')
            ->setParameter('user', $user->getId())
            ->orderBy('quizAttempt.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return QuizAttempt[] Returns an array of QuizAttempt objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('q.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?QuizAttempt
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
