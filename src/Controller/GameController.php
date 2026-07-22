<?php

namespace App\Controller;

use App\Entity\AnswerAttempt;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\QuizAttempt;
use App\Entity\User;
use App\Repository\QuizAttemptRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
final class GameController extends AbstractController
{
    #[Route('/quiz/{quiz}/setup', name: 'game_setup', requirements: ['quiz' => '\d+'])]
    public function setup(?Quiz $quiz, QuizAttemptRepository $quizAttemptRepository, #[CurrentUser] User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        if (!$quiz) {
            $this->addFlash('error', 'Quiz introuvable !');
            return $this->redirectToRoute('quiz_list');
        }

        if ($quiz->getQuestions()->isEmpty()) {
            $this->addFlash('error', 'Le quiz ne contient aucune question, veuillez en ajouter pour pouvoir le lancer !');
            return $this->redirectToRoute('quiz_list');
        }

        # ToDo: Gérer le lancement d'un quiz selon les personnes qui seront autorisés à le faire (ex: uniquement l'auteur du quiz, ou tous les utilisateurs, ou un groupe d'utilisateurs)
        if ($user !== $quiz->getAuthor()) {
            $this->addFlash('error', 'Action non autorisée !');
            return $this->redirectToRoute('quiz_list');
        }

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
            ->add('isShuffled', CheckboxType::class, [
                'label'    => 'Mélanger les questions',
                'required' => false,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mode = $form->getData()['mode'];
            $existingQuizAttempts = $quizAttemptRepository->findBy(['quiz' => $quiz, 'author' => $user, 'mode' => $mode]);
            foreach ($existingQuizAttempts as $existingQuizAttempt) {
                $currentAnswerAttempt = count($existingQuizAttempt->getAnswerAttempts());
                if ($currentAnswerAttempt < $existingQuizAttempt->getMaxScore()) {
                    $this->addFlash('info', 'Reprise de votre quiz en cours !');
                    return $this->redirectToRoute('game_play', ['quizAttempt' => $existingQuizAttempt->getId()]);
                }
            }

            $questions = $quiz->getQuestions();
            $order = [];
            foreach ($questions as $question) {
                $order[] = $question->getId();
            }
            $isShuffled = $form->getData()['isShuffled'];
            if ($isShuffled) {
                shuffle($order);
            }

            $quizAttempt = new QuizAttempt();
            $quizAttempt->setQuiz($quiz);
            $quizAttempt->setAuthor($user);
            $quizAttempt->setMode($form->getData()['mode']);
            $quizAttempt->setQuestionOrder($order);
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

    #[Route('/quiz/play/{quizAttempt}', name: 'game_play', requirements: ['quizAttempt' => '\d+'])]
    public function play(?QuizAttempt $quizAttempt, #[CurrentUser] User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        if (!$quizAttempt) {
            $this->addFlash('error', 'Quiz introuvable !');
            return $this->redirectToRoute('quiz_list');
        }

        # ToDo: Gérer le lancement d'un quiz selon les personnes qui seront autorisés à le faire (ex: uniquement l'auteur du quiz, ou tous les utilisateurs, ou un groupe d'utilisateurs)
        if ($user !== $quizAttempt->getAuthor()) {
            $this->addFlash('error', 'Action non autorisée !');
            return $this->redirectToRoute('quiz_list');
        }

        $currentIndexQuestion = count($quizAttempt->getAnswerAttempts());

        if ($currentIndexQuestion >= $quizAttempt->getMaxScore()) {
            $this->addFlash('info', 'Quiz terminé !');
            return $this->redirectToRoute('game_results', ['quizAttempt' => $quizAttempt->getId()]);
        }

        $questionOrder = $quizAttempt->getQuestionOrder();

        $currentQuestionId = $questionOrder[$currentIndexQuestion];

        $currentQuestion = $quizAttempt->getQuiz()->getQuestions()->filter(function(Question $question) use ($currentQuestionId) {
            return $question->getId() === $currentQuestionId;
        })->first();

        if (!$currentQuestion) {
            $this->addFlash('error', 'Une question de ce quiz a disparu.');
            return $this->redirectToRoute('game_results', ['quizAttempt' => $quizAttempt->getId()]);
        }

        $formBuilder = $this->createFormBuilder();
        $mode = $quizAttempt->getMode();


        if ($mode === 'mode_kanji') {
            $formBuilder
                ->add('givenReading', TextType::class, ['label' => 'Lecture', 'attr' => ['autocomplete' => 'off']])
                ->add('givenTranslation', TextType::class, ['label' => 'Traduction (Français)', 'attr' => ['autocomplete' => 'off']]);
        } elseif ($mode === 'mode_reading') {
            $formBuilder
                ->add('givenKanji', TextType::class, ['label' => 'Kanji', 'attr' => ['autocomplete' => 'off']])
                ->add('givenTranslation', TextType::class, ['label' => 'Traduction (Français)', 'attr' => ['autocomplete' => 'off']]);
        } elseif ($mode === 'mode_translation') {
            $formBuilder
                ->add('givenKanji', TextType::class, ['label' => 'Kanji', 'attr' => ['autocomplete' => 'off']])
                ->add('givenReading', TextType::class, ['label' => 'Lecture', 'attr' => ['autocomplete' => 'off']]);
        }
        $form = $formBuilder->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $answerAttempt = new AnswerAttempt();

            $answerAttempt->setQuizAttempt($quizAttempt);
            $answerAttempt->setQuestion($currentQuestion);

            $data = $form->getData();
            $expectedKanji = trim($currentQuestion->getKanji());
            $expectedReading = trim($currentQuestion->getReading());
            $expectedTranslation = trim(mb_strtolower($currentQuestion->getTranslation()));

            $givenKanji = isset($data['givenKanji']) ? trim($data['givenKanji']) : null;
            $givenReading = isset($data['givenReading']) ? trim($data['givenReading']) : null;
            $givenTranslation = isset($data['givenTranslation']) ? trim(mb_strtolower($data['givenTranslation'])) : null;

            $answerAttempt->setAskedKanji($currentQuestion->getKanji());
            $answerAttempt->setAskedReading($currentQuestion->getReading());
            $answerAttempt->setAskedTranslation($currentQuestion->getTranslation());
            $answerAttempt->setGivenKanji($givenKanji);
            $answerAttempt->setGivenReading($givenReading);
            $answerAttempt->setGivenTranslation($givenTranslation);

            $isCorrect = false;

            if ($mode === 'mode_kanji') {
                $isCorrect = ($expectedReading === $givenReading && $expectedTranslation === $givenTranslation);
            } elseif ($mode === 'mode_reading') {
                $isCorrect = ($expectedKanji === $givenKanji && $expectedTranslation === $givenTranslation);
            } elseif ($mode === 'mode_translation') {
                $isCorrect = ($expectedKanji === $givenKanji && $expectedReading === $givenReading);
            }

            $answerAttempt->setIsCorrect($isCorrect);
            if ($isCorrect) {
                $quizAttempt->setScore($quizAttempt->getScore() + 1);
            }


            $entityManager->persist($answerAttempt);
            $entityManager->flush();

            if ($isCorrect) {
                return $this->redirectToRoute('game_play', ['quizAttempt' => $quizAttempt->getId()]);

            } else {
                return $this->redirectToRoute('game_correction', ['answerAttempt' => $answerAttempt->getId()]);
            }
        }

        return $this->render('game/play.html.twig', [
            'quizAttempt' => $quizAttempt,
            'currentIndexQuestion' => $currentIndexQuestion,
            'currentQuestion' => $currentQuestion,
            'form' => $form,
        ]);
    }

    #[Route('/quiz/result/{quizAttempt}', name: 'game_results', requirements: ['quizAttempt' => '\d+'])]
    public function result(?QuizAttempt $quizAttempt, #[CurrentUser] User $user): Response
    {
        if (!$quizAttempt) {
            $this->addFlash('error', 'Quiz introuvable !');
            return $this->redirectToRoute('home');
        }

        if ($user !== $quizAttempt->getAuthor()) {
            $this->addFlash('error', 'Accès refusé !');
            return $this->redirectToRoute('home');
        }

        return $this->render('game/result.html.twig', [
            'quizAttempt' => $quizAttempt,
        ]);
    }

    #[Route('/quiz/history', name: 'game_history', requirements: ['quizAttempt' => '\d+'])]
    public function history(#[CurrentUser] User $user, QuizAttemptRepository $quizAttemptRepository): Response
    {
        $quizAttemptsList = $quizAttemptRepository->findBy(['author' => $user]);

        return $this->render('game/history.html.twig', [
            'quizAttemptsList' => $quizAttemptsList,
        ]);
    }

    #[Route('/quiz/game/delete/{quizAttempt}', name: 'game_delete', methods: ['POST'])]
    public function delete(#[CurrentUser] User $user, EntityManagerInterface $entityManager, Request $request, ?QuizAttempt $quizAttempt): Response
    {
        if (!$quizAttempt) {
            $this->addFlash('error', 'Quiz introuvable !');
            return $this->redirectToRoute('home');
        }

        if ($quizAttempt->getAuthor() !== $user) {
            $this->addFlash('error', 'Action non autorisée !');
            return $this->redirectToRoute('quiz_list');
        }

        if ($this->isCsrfTokenValid('delete' . $quizAttempt->getId(), $request->request->get('_token'))) {
            $entityManager->remove($quizAttempt);
            $entityManager->flush();
            $this->addFlash('success', 'Votre quiz a bien été supprimé');
        } else {
            $this->addFlash('error', 'Action non autorisée (Token CSRF invalide).');
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

    #[Route('/quiz/game/correction/{answerAttempt}', name: 'game_correction')]
    public function correction(#[CurrentUser] User $user, ?AnswerAttempt $answerAttempt): Response
    {
        if (!$answerAttempt) {
            $this->addFlash('error', 'Réponse introuvable !');
            return $this->redirectToRoute('home');
        }

        if ($answerAttempt->getQuizAttempt()->getAuthor() !== $user) {
            $this->addFlash('error', 'Action non autorisée !');
            return $this->redirectToRoute('quiz_list');
        }


        return $this->render('game/correction.html.twig', [
            'answerAttempt' => $answerAttempt,
        ]);
    }

    #[Route('/quiz/reset/{quizAttempt}', name: 'game_reset', methods: ['POST'])]
    public function reset(#[CurrentUser] User $user, ?QuizAttempt $quizAttempt, EntityManagerInterface $entityManager, Request $request): Response
    {
        if (!$quizAttempt) {
            $this->addFlash('error', 'Quiz introuvable !');
            return $this->redirectToRoute('home');
        }

        if ($quizAttempt->getAuthor() !== $user) {
            $this->addFlash('error', 'Action non autorisée !');
            return $this->redirectToRoute('quiz_list');
        }

        if (!$this->isCsrfTokenValid('reset' . $quizAttempt->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée (Token CSRF invalide).');
            return $this->redirectToRoute('game_play', ['quizAttempt' => $quizAttempt->getId()]);
        }

        $questions = $quizAttempt->getQuiz()->getQuestions();
        $order = [];
        foreach ($questions as $question) {
            $order[] = $question->getId();
        }

        if ($request->request->get('shuffle') == "1") {
            shuffle($order);
        }

        $newQuizAttempt = new QuizAttempt();
        $newQuizAttempt->setAuthor($user);
        $newQuizAttempt->setMode($quizAttempt->getMode());
        $newQuizAttempt->setQuiz($quizAttempt->getQuiz());

        $newQuizAttempt->setQuestionOrder($order);

        $newQuizAttempt->setMaxScore($quizAttempt->getMaxScore());
        $newQuizAttempt->setScore(0);
        $entityManager->persist($newQuizAttempt);

        $entityManager->remove($quizAttempt);
        $entityManager->flush();

        return $this->redirectToRoute('game_play', ['quizAttempt' => $newQuizAttempt->getId()]);
    }
}
