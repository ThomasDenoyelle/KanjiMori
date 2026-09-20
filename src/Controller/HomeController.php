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
    /**
     * Displays the home page with in-progress quiz attempts.
     *
     * @param User                  $user                  authenticated user whose attempts are loaded
     * @param QuizAttemptRepository $quizAttemptRepository repository used to load quiz attempts
     */
    #[Route('/', name: 'home')]
    public function home(#[CurrentUser] User $user, QuizAttemptRepository $quizAttemptRepository): Response
    {
        $allAttempts = $quizAttemptRepository->findAllQuizAttemptByUser($user);

        $inProgressAttempts = array_filter($allAttempts, function ($attempt) {
            $currentAnswers = count($attempt->getAnswerAttempts());

            return $currentAnswers < $attempt->getMaxScore();
        });

        return $this->render('home/index.html.twig', [
            'inProgressAttempts' => $inProgressAttempts,
        ]);
    }
}
