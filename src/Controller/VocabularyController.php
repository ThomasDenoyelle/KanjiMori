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

    #[Route('/my-library/vocabulary/generate', name: 'library_vocabulary_generate', methods: ['POST'])]
    public function generate(
        Request $request,
        ApiService $apiService,
        EntityManagerInterface $em,
        #[CurrentUser] User $user
    ): Response {
        $text = (string) $request->request->get('japanese_text', '');
        $title = (string) $request->request->get('sheet_title', 'Nouvelle fiche');

        if (empty(trim($text))) {
            $this->addFlash('danger', 'Veuillez renseigner un texte en japonais.');
            return $this->redirectToRoute('library_vocabulary_list');
        }

        $vocabulary = $apiService->analyseText($text);

        $sheet = new VocabularySheet();
        $sheet->setAuthor($user);
        $sheet->setTitle($title);

        foreach ($vocabulary as $itemData) {
            $item = new VocabularyItem();
            $item->setKanji($itemData['kanji'] ?? '');
            $item->setReading($itemData['reading'] ?? '');
            $item->setTranslation($itemData['translation'] ?? '');
            $sheet->addItem($item);
        }

        $form = $this->createForm(VocabularySheetType::class, $sheet, [
            'action' => $this->generateUrl('library_vocabulary_new'),
        ]);

        return $this->render('vocabulary/form.html.twig', [
            'form' => $form,
            'sheet' => $sheet,
            'isNew' => true,
        ]);
    }

    #[Route('/my-library/vocabulary/{vocabularySheet}/update', name: 'library_vocabulary_update', requirements: ['vocabularySheet' => '\d+'])]
    public function update(#[CurrentUser] User $user, Request $request, EntityManagerInterface $entityManager, VocabularySheet $vocabularySheet): Response
    {
        if ($vocabularySheet->getAuthor() !== $user) {
            $this->addFlash("error", "Vous n'avez pas la permission de modifier cette fiche.");
            return $this->redirectToRoute('library_vocabulary_list');
        }

        $form = $this->createForm(VocabularySheetType::class, $vocabularySheet);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('library_vocabulary_list');
        }

        return $this->render('vocabulary/form.html.twig', [
            'form' => $form,
            'sheet' => $vocabularySheet,
            'isNew' => false,
        ]);
    }
}
