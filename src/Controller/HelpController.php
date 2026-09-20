<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Form\FeedbackType;
use App\Repository\FeedbackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HelpController extends AbstractController
{
    /**
     * Creates a new feedback entry for the current user.
     *
     * @param EntityManagerInterface $entityManager entity manager used to persist the feedback
     * @param Request                $request       incoming feedback form submission
     * @param string                 $type          feedback type to create
     */
    #[Route('/feedbacks/{type}/new', name: 'feedback_new')]
    #[IsGranted('ROLE_USER')]
    public function feedbackNew(EntityManagerInterface $entityManager, Request $request, string $type): Response
    {
        if ('idea' != $type && 'bug' != $type) {
            return $this->redirectToRoute('home');
        }
        $feedback = new Feedback();
        $feedback->setAuthor($this->getUser());
        $feedback->setType($type);
        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($feedback);
            $entityManager->flush();
            $this->addFlash('success', 'Votre retour à bien été sauvegardé !');

            return $this->redirectToRoute('home');
        }

        return $this->render('help/feedback_new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Displays the list of all feedback entries.
     *
     * @param FeedbackRepository $feedbackRepository repository used to load feedback entries
     */
    #[Route('/admin/feedbacks', name: 'feedback_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function feedbackList(FeedbackRepository $feedbackRepository): Response
    {
        $feedbacksList = $feedbackRepository->findAll();

        return $this->render('help/feedback_list.html.twig', [
            'feedbacksList' => $feedbacksList,
        ]);
    }

    /**
     * Displays a single feedback entry.
     *
     * @param Feedback $feedback feedback entry to display
     */
    #[Route('/admin/feedbacks/{feedback}', name: 'feedback_show')]
    #[IsGranted('ROLE_ADMIN')]
    public function feedbackShow(Feedback $feedback): Response
    {
        return $this->render('help/feedback_show.html.twig', [
            'feedback' => $feedback,
        ]);
    }

    /**
     * Toggles the validation state of a feedback entry.
     *
     * @param Feedback               $feedback      feedback entry to validate or unvalidate
     * @param EntityManagerInterface $entityManager entity manager used to persist the change
     */
    #[Route('/admin/feedbacks/{feedback}/validate', name: 'feedback_validate', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function feedbackValidate(Feedback $feedback, EntityManagerInterface $entityManager): Response
    {
        if ($feedback->isValid()) {
            $feedback->setIsValid(false);
        } else {
            $feedback->setIsValid(true);
        }
        $entityManager->flush();

        return $this->redirectToRoute('feedback_show', ['feedback' => $feedback->getId()]);
    }

    /**
     * Displays the user guide page.
     */
    #[Route('/guide', name: 'guide')]
    public function guide(): Response
    {
        return $this->render('help/guide.html.twig');
    }

    /**
     * Displays the legal mentions page.
     */
    #[Route('/legal-mentions', name: 'legal_mentions')]
    public function legalMentions(): Response
    {
        return $this->render('help/legal_mentions.html.twig');
    }

    /**
     * Displays the privacy policy page.
     */
    #[Route('/privacy', name: 'privacy')]
    public function privacy(): Response
    {
        return $this->render('help/privacy.html.twig');
    }
}
