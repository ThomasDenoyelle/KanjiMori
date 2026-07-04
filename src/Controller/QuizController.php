<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\User;
use App\Form\QuizType;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
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

    #[Route('/quiz/new', name: 'quiz_new')]
    public function new(#[CurrentUser] User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        $quiz = new Quiz();
        $quiz->setAuthor($user);

        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($quiz);
            $entityManager->flush();

            $this->addFlash('success', 'Votre quiz a bien été créé avec ses questions !');
            return $this->redirectToRoute('quiz_list');
        }

        return $this->render('quiz/new.html.twig', [
            'form' => $form,
        ]);
    }
}
