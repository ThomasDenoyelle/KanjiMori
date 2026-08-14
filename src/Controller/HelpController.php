<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Form\FeedbackType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HelpController extends AbstractController
{
    #[Route('/feedback/{type}/new', name: 'feedback_new')]
    #[IsGranted('ROLE_USER')]
    public function feedback(EntityManagerInterface $entityManager, Request $request, string $type): Response
    {
        if ($type != 'idea' && $type != 'bug') {
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
            return $this->redirectToRoute('home');
        }

        return $this->render('help/feedback_new.html.twig', [
            'form' => $form,
        ]);
    }
}
