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

#[IsGranted('ROLE_USER')]
final class GameController extends AbstractController
{
    /**
     * Prepares a quiz attempt and game settings.
     *
     * @param Quiz|null              $quiz                  quiz to launch
     * @param QuizAttemptRepository  $quizAttemptRepository repository used to resume existing attempts
     * @param User                   $user                  authenticated user starting the game
     * @param EntityManagerInterface $entityManager         entity manager used to persist a new attempt
     * @param Request                $request               incoming setup form submission
     */
    #[Route('/quiz/{quiz}/setup', name: 'game_setup', requirements: ['quiz' => '\d+'])]
    public function setup(?Quiz $quiz, QuizAttemptRepository $quizAttemptRepository, #[CurrentUser] User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        if (!$quiz) {
            $this->addFlash('error', 'Quiz introuvable !');

            return $this->redirectToRoute('home');
        }

        if ($quiz->getQuestions()->isEmpty()) {
            $this->addFlash('error', 'Le quiz ne contient aucune question, veuillez en ajouter pour pouvoir le lancer !');

            return $this->redirectToRoute('home');
        }

        if ($user !== $quiz->getAuthor() && !$quiz->isPublic()) {
            $this->addFlash('error', 'Action non autorisée !');

            return $this->redirectToRoute('home');
        }

        $form = $this->createFormBuilder()
            ->add('mode', ChoiceType::class, [
                'choices' => [
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
                ],
            ])
            ->add('isShuffled', CheckboxType::class, [
                'label' => 'Mélanger les questions',
                'required' => false,
                'attr' => [
                    'class' => 'checkbox checkbox-primary',
                ],
                'label_attr' => [
                    'class' => 'label',
                ],
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

    /**
     * Plays the current question of a quiz attempt.
     *
     * @param QuizAttempt|null       $quizAttempt   quiz attempt being played
     * @param User                   $user          authenticated user answering the quiz
     * @param EntityManagerInterface $entityManager entity manager used to save the answer attempt
     * @param Request                $request       incoming answer submission
     */
    #[Route('/quiz/play/{quizAttempt}', name: 'game_play', requirements: ['quizAttempt' => '\d+'])]
    public function play(?QuizAttempt $quizAttempt, #[CurrentUser] User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        if (!$quizAttempt) {
            $this->addFlash('error', 'Quiz introuvable !');

            return $this->redirectToRoute('home');
        }

        if ($user !== $quizAttempt->getAuthor()) {
            $this->addFlash('error', 'Action non autorisée !');

            return $this->redirectToRoute('home');
        }

        $currentIndexQuestion = count($quizAttempt->getAnswerAttempts());

        if ($currentIndexQuestion >= $quizAttempt->getMaxScore()) {
            $this->addFlash('info', 'Quiz terminé !');

            return $this->redirectToRoute('game_results', ['quizAttempt' => $quizAttempt->getId()]);
        }

        $questionOrder = $quizAttempt->getQuestionOrder();

        $currentQuestionId = $questionOrder[$currentIndexQuestion];

        $currentQuestion = $quizAttempt->getQuiz()->getQuestions()->filter(function (Question $question) use ($currentQuestionId) {
            return $question->getId() === $currentQuestionId;
        })->first();

        if (!$currentQuestion) {
            $this->addFlash('error', 'Une question de ce quiz a disparu.');

            return $this->redirectToRoute('game_results', ['quizAttempt' => $quizAttempt->getId()]);
        }

        $formBuilder = $this->createFormBuilder();
        $mode = $quizAttempt->getMode();

        if ('mode_kanji' === $mode) {
            $formBuilder
                ->add('givenReading', TextType::class, ['label' => 'Lecture', 'attr' => ['autocomplete' => 'off']])
                ->add('givenTranslation', TextType::class, ['label' => 'Traduction (Français)', 'attr' => ['autocomplete' => 'off']]);
        } elseif ('mode_reading' === $mode) {
            $formBuilder
                ->add('givenKanji', TextType::class, ['label' => 'Kanji', 'attr' => ['autocomplete' => 'off']])
                ->add('givenTranslation', TextType::class, ['label' => 'Traduction (Français)', 'attr' => ['autocomplete' => 'off']]);
        } elseif ('mode_translation' === $mode) {
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

            if ('mode_kanji' === $mode) {
                $isCorrect = ($expectedReading === $givenReading && $expectedTranslation === $givenTranslation);
            } elseif ('mode_reading' === $mode) {
                $isCorrect = ($expectedKanji === $givenKanji && $expectedTranslation === $givenTranslation);
            } elseif ('mode_translation' === $mode) {
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
            }

            return $this->redirectToRoute('game_correction', ['answerAttempt' => $answerAttempt->getId()]);
        }

        return $this->render('game/play.html.twig', [
            'quizAttempt' => $quizAttempt,
            'currentIndexQuestion' => $currentIndexQuestion,
            'currentQuestion' => $currentQuestion,
            'form' => $form,
        ]);
    }

    /**
     * Displays the result summary for a quiz attempt.
     *
     * @param QuizAttempt|null $quizAttempt quiz attempt to display
     * @param User             $user        authenticated user viewing the result
     */
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

    /**
     * Displays the current user's quiz attempt history.
     *
     * @param User                  $user                  authenticated user whose history is loaded
     * @param QuizAttemptRepository $quizAttemptRepository repository used to load quiz attempts
     */
    #[Route('/quiz/history', name: 'game_history', requirements: ['quizAttempt' => '\d+'])]
    public function history(#[CurrentUser] User $user, QuizAttemptRepository $quizAttemptRepository): Response
    {
        $quizAttemptsList = $quizAttemptRepository->findBy(['author' => $user]);

        return $this->render('game/history.html.twig', [
            'quizAttemptsList' => $quizAttemptsList,
        ]);
    }

    /**
     * Deletes a quiz attempt owned by the current user.
     *
     * @param User                   $user          authenticated user requesting the deletion
     * @param EntityManagerInterface $entityManager entity manager used to remove the attempt
     * @param Request                $request       incoming delete request
     * @param QuizAttempt|null       $quizAttempt   quiz attempt to delete
     */
    #[Route('/quiz/game/delete/{quizAttempt}', name: 'game_delete', methods: ['POST'])]
    public function delete(#[CurrentUser] User $user, EntityManagerInterface $entityManager, Request $request, ?QuizAttempt $quizAttempt): Response
    {
        if (!$quizAttempt) {
            $this->addFlash('error', 'Quiz introuvable !');

            return $this->redirectToRoute('home');
        }

        if ($quizAttempt->getAuthor() !== $user) {
            $this->addFlash('error', 'Action non autorisée !');

            return $this->redirectToRoute('library_quiz_list');
        }

        if ($this->isCsrfTokenValid('delete'.$quizAttempt->getId(), $request->request->get('_token'))) {
            $entityManager->remove($quizAttempt);
            $entityManager->flush();
            $this->addFlash('success', 'Votre quiz a bien été supprimé');
        } else {
            $this->addFlash('error', 'Action non autorisée (Token CSRF invalide).');
        }

        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    /**
     * Shows the correction for a submitted answer.
     *
     * @param User               $user          authenticated user reviewing the correction
     * @param AnswerAttempt|null $answerAttempt answer attempt to review
     */
    #[Route('/quiz/game/correction/{answerAttempt}', name: 'game_correction')]
    public function correction(#[CurrentUser] User $user, ?AnswerAttempt $answerAttempt): Response
    {
        if (!$answerAttempt) {
            $this->addFlash('error', 'Réponse introuvable !');

            return $this->redirectToRoute('home');
        }

        if ($answerAttempt->getQuizAttempt()->getAuthor() !== $user) {
            $this->addFlash('error', 'Action non autorisée !');

            return $this->redirectToRoute('library_quiz_list');
        }

        return $this->render('game/correction.html.twig', [
            'answerAttempt' => $answerAttempt,
        ]);
    }

    /**
     * Resets a quiz attempt by creating a fresh one.
     *
     * @param User                   $user          authenticated user resetting the attempt
     * @param QuizAttempt|null       $quizAttempt   quiz attempt to reset
     * @param EntityManagerInterface $entityManager entity manager used to replace the attempt
     * @param Request                $request       incoming reset request
     */
    #[Route('/quiz/reset/{quizAttempt}', name: 'game_reset', methods: ['POST'])]
    public function reset(#[CurrentUser] User $user, ?QuizAttempt $quizAttempt, EntityManagerInterface $entityManager, Request $request): Response
    {
        if (!$quizAttempt) {
            $this->addFlash('error', 'Quiz introuvable !');

            return $this->redirectToRoute('home');
        }

        if ($quizAttempt->getAuthor() !== $user) {
            $this->addFlash('error', 'Action non autorisée !');

            return $this->redirectToRoute('library_quiz_list');
        }

        if (!$this->isCsrfTokenValid('reset'.$quizAttempt->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée (Token CSRF invalide).');

            return $this->redirectToRoute('game_play', ['quizAttempt' => $quizAttempt->getId()]);
        }

        $questions = $quizAttempt->getQuiz()->getQuestions();
        $order = [];
        foreach ($questions as $question) {
            $order[] = $question->getId();
        }

        if ('1' == $request->request->get('shuffle')) {
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

    /**
     * Displays a study view for a quiz.
     *
     * @param Quiz $quiz quiz to study
     * @param User $user authenticated user viewing the study mode
     */
    #[Route('/quiz/{quiz}/study', name: 'game_study', requirements: ['quiz' => '\d+'])]
    public function study(Quiz $quiz, #[CurrentUser] User $user): Response
    {
        $hasAccess = false;
        if ($quiz->isPublic() || $quiz->getAuthor() === $user) {
            $hasAccess = true;
        } else {
            foreach ($quiz->getFolders() as $folder) {
                if ($folder->getAuthor() === $user || $folder->getMembers()->contains($user)) {
                    $hasAccess = true;
                    break;
                }
            }
        }
        if (!$hasAccess) {
            $this->addFlash('error', 'Vous n\'avez pas accès à ce quiz.');

            return $this->redirectToRoute('home');
        }

        $questionsData = [];
        foreach ($quiz->getQuestions() as $question) {
            $questionsData[] = [
                'kanji' => $question->getKanji(),
                'reading' => $question->getReading(),
                'translation' => $question->getTranslation(),
            ];
        }

        return $this->render('game/study.html.twig', [
            'quiz' => $quiz,
            'questionsJson' => json_encode($questionsData),
        ]);
    }
}
