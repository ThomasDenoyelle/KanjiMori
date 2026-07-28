<?php

namespace App\Controller;

use App\Entity\Folder;
use App\Entity\Quiz;
use App\Entity\User;
use App\Form\FolderType;
use App\Repository\FolderRepository;
use App\Repository\QuizRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class FolderController extends AbstractController
{
    #[Route('/my-library/folder', name: 'library_folder_list')]
    public function myFolder(#[CurrentUser] User $user, FolderRepository $folderRepository): Response
    {
        $folderList = $folderRepository->findBy(['author' => $user]);

        $newFolderForm = $this->createForm(FolderType::class, null, ['user' => $user]);

        return $this->render('folder/library_list.html.twig', [
            'folderList' => $folderList,
            'newFolderForm' => $newFolderForm,
        ]);
    }

    #[Route('/explore/class', name: 'explore_class_list')]
    public function exploreClass(#[CurrentUser] User $user, FolderRepository $folderRepository): Response
    {
        $classList = $folderRepository->findAllJoindedClassByUser($user);

        return $this->render('folder/explore_list.html.twig', [
            'classList' => $classList,
        ]);
    }

    #[Route('/my-library/folder/new', name: 'library_folder_new')]
    public function new(#[CurrentUser] User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $folder = new Folder();
        $folder->setAuthor($user);
        $form = $this->createForm(FolderType::class, $folder, ['user' => $user]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($folder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('library_folder_list');
    }

    #[Route('/my-library/folder/{folder}/delete', name: 'library_folder_delete', requirements: ['folder' => '\d+'], methods: ['POST'])]
    public function delete(#[CurrentUser] User $user, Folder $folder, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($folder->getAuthor() !== $user) {
            $this->addFlash('error','Action non autorisé ou dossier introuvable !');
            return $this->redirectToRoute('library_folder_list');
        }

        if ($this->isCsrfTokenValid('delete_folder_' . $folder->getId(), $request->request->get('_token'))) {
            $entityManager->remove($folder);
            $entityManager->flush();
            $this->addFlash('success', 'Votre dossier a bien été supprimé');
        } else {
            $this->addFlash('error', 'Action non autorisée (Token CSRF invalide).');
        }

        return $this->redirectToRoute('library_folder_list');
    }

    #[Route('/my-library/folder/{folder}/show', name: 'library_folder_show')]
    public function show(Folder $folder, #[CurrentUser] User $user, QuizRepository $quizRepository, UserRepository $userRepository): Response
    {
        if ($folder->getAuthor() !== $user && !$folder->getMembers()->contains($user)) {
            $this->addFlash('error','Action non autorisé ou dossier introuvable !');
            return $this->redirectToRoute('library_folder_list');
        }

        $quizList = $quizRepository->findAllQuizByUser($user);

        $allUsers = $userRepository->findAllUserExceptCurrent($user);

        $updateFolderForm = $this->createForm(FolderType::class, $folder, ['user' => $user]);

        return $this->render('folder/show.html.twig', [
            'folder' => $folder,
            'quizList' => $quizList,
            'updateFolderForm' => $updateFolderForm,
            'allUsers' => $allUsers,
        ]);
    }

    #[Route('/my-library/folder/{folder}/toggle-quiz/{quiz}', name: 'library_folder_toggle_quiz', methods: ['POST'])]
    public function toggleQuiz(Folder $folder, Quiz $quiz, #[CurrentUser] User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($folder->getAuthor() !== $user || $quiz->getAuthor() !== $user) {
            $this->addFlash('error', 'Action non autorisée !');
            return $this->redirectToRoute('library_folder_list');
        }

        if (!$this->isCsrfTokenValid('toggle' . $folder->getId() . $quiz->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée (Token CSRF invalide).');
            return $this->redirectToRoute('library_folder_show', ['folder' => $folder->getId()]);
        }

        if ($folder->getQuizzes()->contains($quiz)) {
            $folder->removeQuiz($quiz);
        } else {
            $folder->addQuiz($quiz);
        }

        $entityManager->flush();

        return $this->redirectToRoute('library_folder_show', [
            'folder' => $folder->getId(),
        ]);
    }

    #[Route('/my-library/folder/{folder}/update', name: 'library_folder_update', requirements: ['folder' => '\d+'], methods: ['POST'])]
    public function update(Folder $folder, #[CurrentUser] User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($folder->getAuthor() !== $user) {
            $this->addFlash('error', 'Action non autorisée !');
            return $this->redirectToRoute('library_folder_list');
        }

        $form = $this->createForm(FolderType::class, $folder, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Votre dossier a bien été mis à jour');
        }

        return $this->redirectToRoute('library_folder_show', ['folder' => $folder->getId()]);
    }
}
