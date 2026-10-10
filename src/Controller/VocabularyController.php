<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\VocabularySheetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class VocabularyController extends AbstractController
{
    #[Route('/my-library/vocabulary', name: 'library_vocabulary_list')]
    public function myVocabulary(#[CurrentUser] User $user, VocabularySheetRepository $vocabularySheetRepository): Response
    {
        $vocabularySheets = $vocabularySheetRepository->findBy(['user' => $user]);

        return $this->render('vocabulary/library_list.html.twig', [
            'vocabularySheets' => $vocabularySheets,
        ]);
    }
}
