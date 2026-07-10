<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\QuizAttemptRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;


final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(#[CurrentUser] User $user, QuizAttemptRepository $quizAttemptRepository): Response
    {
        $allAttempts = $quizAttemptRepository->findBy(
            ['author' => $user],
            ['createdAt' => 'DESC']
        );

        $inProgressAttempts = array_filter($allAttempts, function($attempt) {
            $currentAnswers = count($attempt->getAnswerAttempts());
            return $currentAnswers > 0 && $currentAnswers < $attempt->getMaxScore();
        });

        return $this->render('home/index.html.twig', [
            'inProgressAttempts' => $inProgressAttempts,
        ]);
    }
}
