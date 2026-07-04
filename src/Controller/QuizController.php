<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\QuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
final class QuizController extends AbstractController
{
    #[Route('/quiz', name: 'quiz_list')]
    public function list(#[CurrentUser] User $user, QuizRepository $quizRepository): Response
    {
        $quizList = $quizRepository->findBy(['author' => $user]);

        return $this->render('quiz/list.html.twig', [
            'quizList' => $quizList,
        ]);
    }
}
