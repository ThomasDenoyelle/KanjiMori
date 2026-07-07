<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\QuizAttempt;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
final class GameController extends AbstractController
{
    #[Route('/quiz/{quiz}/setup', name: 'game_setup')]
    public function setup(?Quiz $quiz, #[CurrentUser] User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        if (!$quiz) {
            $this->addFlash('error', 'Quiz introuvable !');
            return $this->redirectToRoute('quiz_list');
        }

        # ToDo: Gérer le lancement d'un quiz selon les personnes qui seront autorisés à le faire (ex: uniquement l'auteur du quiz, ou tous les utilisateurs, ou un groupe d'utilisateurs)
        if ($user !== $quiz->getAuthor()) {
            $this->addFlash('error', 'Action non autorisée !');
            return $this->redirectToRoute('quiz_list');
        }

        $quizAttempt = new QuizAttempt();
        $quizAttempt->setQuiz($quiz);
        $quizAttempt->setAuthor($user);

        $form = $this->createFormBuilder()
            ->add('mode', ChoiceType::class, [
                'choices'  => [
                    'Kanji' => 'mode_kanji',
                    'Lecture' => 'mode_reading',
                    'Traduction' => 'mode_translation',
                ],
                'label' => 'Mode de jeu',
                'attr' => [
                    'class' => 'select w-full',
                ],
                'label_attr' => [
                    'class' => 'label',
                ]
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $quizAttempt->setMode($form->getData()['mode']);
            $quizAttempt->setScore(0);
            $quizAttempt->setMaxScore(count($quiz->getQuestions()));

            $entityManager->persist($quizAttempt);
            $entityManager->flush();
            return $this->redirectToRoute('game_play', ['quizAttempt' => $quizAttempt->getId()]);
        }

        return $this->render('game/setup.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
        ]);
    }
}
