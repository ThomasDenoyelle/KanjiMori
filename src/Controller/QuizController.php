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

            $this->addFlash('success', 'Votre quiz a bien été créé avec ses questions');
            return $this->redirectToRoute('quiz_list');
        }

        return $this->render('quiz/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/quiz/{quiz}/update', name: 'quiz_update')]
    public function update(#[CurrentUser] User $user, EntityManagerInterface $entityManager, Request $request, ?Quiz $quiz): Response
    {
        if (!$quiz || $quiz->getAuthor() !== $user) {
            $this->addFlash('error', 'Action non autorisée ou quiz introuvable !');
            return $this->redirectToRoute('quiz_list');
        }

        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Votre quiz a bien été modifié');
            return $this->redirectToRoute('quiz_list');
        }

        return $this->render('quiz/update.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/quiz/{quiz}/delete', name: 'quiz_delete', methods: ['POST'])]
    public function delete(#[CurrentUser] User $user, EntityManagerInterface $entityManager, Request $request, ?Quiz $quiz): Response
    {
        if (!$quiz || $quiz->getAuthor() !== $user) {
            $this->addFlash('error', 'Action non autorisée ou quiz introuvable !');
            return $this->redirectToRoute('quiz_list');
        }

        if ($this->isCsrfTokenValid('delete' . $quiz->getId(), $request->request->get('_token'))) {
            $entityManager->remove($quiz);
            $entityManager->flush();
            $this->addFlash('success', 'Votre quiz a bien été supprimé');
        } else {
            $this->addFlash('error', 'Action non autorisée (Token CSRF invalide).');
        }

        return $this->redirectToRoute('quiz_list');
    }


}
