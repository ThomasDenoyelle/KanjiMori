<?php

namespace App\Repository;

use App\Entity\Quiz;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quiz>
 */
class QuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }

    public function findAllQuizByUser(User $user) : array
    {
        return $this->createQueryBuilder('quiz')
            ->leftJoin('quiz.questions', 'questions')
            ->select('quiz, questions')
            ->where('quiz.author = :user')
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getResult();

    }

    public function findAllPublicQuiz(User $user) : array
    {
        return $this->createQueryBuilder('quiz')
            ->leftJoin('quiz.questions', 'questions')
            ->select('quiz, questions')
            ->where('quiz.author != :user')
            ->andWhere('quiz.isPublic = true')
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getResult();
    }

    public function findPublicQuizzesWithQuestionsByUser(User $user): array
    {
        return $this->createQueryBuilder('q')
            ->leftJoin('q.questions', 'quest')
            ->addSelect('quest')
            ->andWhere('q.author = :user')
            ->andWhere('q.isPublic = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
            ;
    }

}
