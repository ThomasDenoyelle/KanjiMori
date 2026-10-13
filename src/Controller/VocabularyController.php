<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\VocabularyItem;
use App\Entity\VocabularySheet;
use App\Form\VocabularySheetType;
use App\Repository\VocabularySheetRepository;
use App\Service\ApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class VocabularyController extends AbstractController
{
    #[Route('/my-library/vocabulary', name: 'library_vocabulary_list')]
    public function myVocabulary(#[CurrentUser] User $user, VocabularySheetRepository $vocabularySheetRepository): Response
    {
        $vocabularySheetList = $vocabularySheetRepository->findBy(['author' => $user]);

        return $this->render('vocabulary/library_list.html.twig', [
            'vocabularySheetList' => $vocabularySheetList,
        ]);
    }

    #[Route('/my-library/vocabulary/new', name: 'library_vocabulary_new')]
    public function new(#[CurrentUser] User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $vocabularySheet = new VocabularySheet();
        $vocabularySheet->setAuthor($user);
        $form = $this->createForm(VocabularySheetType::class, $vocabularySheet);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($vocabularySheet);
            $entityManager->flush();

            return $this->redirectToRoute('library_vocabulary_list');
        }

        return $this->render('vocabulary/form.html.twig', [
            'form' => $form,
            'sheet' => $vocabularySheet,
            'isNew' => true,
        ]);
    }
}
