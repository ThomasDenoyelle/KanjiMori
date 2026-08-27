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
    /**
     * Displays the current user's folders and the creation form.
     *
     * @param User $user Authenticated user requesting the page.
     * @param FolderRepository $folderRepository Repository used to load the user's folders.
     */
    #[Route('/my-library/folder', name: 'library_folder_list')]
    public function myFolder(#[CurrentUser] User $user, FolderRepository $folderRepository): Response
    {
        $folderList = $folderRepository->findAllFolderByUser($user);

        $newFolderForm = $this->createForm(FolderType::class, null, ['user' => $user]);

        return $this->render('folder/library_list.html.twig', [
            'folderList' => $folderList,
            'newFolderForm' => $newFolderForm,
        ]);
    }

    /**
     * Displays the public classes the current user has joined.
     *
     * @param User $user Authenticated user requesting the page.
     * @param FolderRepository $folderRepository Repository used to load joined public classes.
     */
    #[Route('/explore/class', name: 'explore_class_list')]
    public function exploreClass(#[CurrentUser] User $user, FolderRepository $folderRepository): Response
    {
        $classList = $folderRepository->findAllJoindedClassByUser($user);
        $class = new Folder();
        $class->setIsPublic(true);
        $newFolderForm = $this->createForm(FolderType::class, $class, ['user' => $user]);

        return $this->render('folder/explore_list.html.twig', [
            'classList' => $classList,
            'newFolderForm' => $newFolderForm,
        ]);
    }

    /**
     * Creates a new folder for the current user.
     *
     * @param User $user Authenticated user creating the folder.
     * @param Request $request Incoming form submission.
     * @param EntityManagerInterface $entityManager Entity manager used to persist the folder.
     */
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

        return $this->redirectToRoute('library_folder_show', ['folder' => $folder->getId()]);
    }

    /**
     * Deletes a folder owned by the current user.
     *
     * @param User $user Authenticated user requesting the deletion.
     * @param Folder $folder Folder to delete.
     * @param EntityManagerInterface $entityManager Entity manager used to remove the folder.
     * @param Request $request Incoming delete request.
     */
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

    /**
     * Shows a folder with its quizzes, members, and edit form.
     *
     * @param Folder $folder Folder to display.
     * @param User $user Authenticated user viewing the folder.
     * @param QuizRepository $quizRepository Repository used to load the user's quizzes.
     * @param UserRepository $userRepository Repository used to load related users.
     * @param FolderRepository $folderRepository Repository used to load the full folder data.
     */
    #[Route('/my-library/folder/{folder}/show', name: 'library_folder_show')]
    public function show(Folder $folder, #[CurrentUser] User $user, QuizRepository $quizRepository, UserRepository $userRepository, FolderRepository $folderRepository): Response
    {
        $currentFolder = $folderRepository->findFolderWithEverything($folder);

        if ($currentFolder->getAuthor() !== $user && !$currentFolder->getMembers()->contains($user)) {
            $this->addFlash('error','Action non autorisé ou dossier introuvable !');
            return $this->redirectToRoute('library_folder_list');
        }

        $quizList = $quizRepository->findAllQuizByUser($user);

        $mutualFriends = $userRepository->findMutualFollowers($user);

        $updateFolderForm = $this->createForm(FolderType::class, $currentFolder, ['user' => $user]);

        return $this->render('folder/show.html.twig', [
            'folder' => $currentFolder,
            'quizList' => $quizList,
            'updateFolderForm' => $updateFolderForm,
            'mutualFriends' => $mutualFriends,
        ]);
    }

    /**
     * Toggles a quiz inside a folder.
     *
     * @param Folder $folder Folder being updated.
     * @param Quiz $quiz Quiz being added to or removed from the folder.
     * @param User $user Authenticated user managing the folder.
     * @param EntityManagerInterface $entityManager Entity manager used to flush the change.
     * @param Request $request Incoming toggle request.
     */
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

    /**
     * Updates a folder's details.
     *
     * @param Folder $folder Folder being updated.
     * @param User $user Authenticated user editing the folder.
     * @param Request $request Incoming form submission.
     * @param EntityManagerInterface $entityManager Entity manager used to flush changes.
     */
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
            if (!$folder->isPublic()) { $folder->getMembers()->clear(); }
            $entityManager->flush();
            $this->addFlash('success', 'Votre dossier a bien été mis à jour');
        }

        return $this->redirectToRoute('library_folder_show', ['folder' => $folder->getId()]);
    }

    /**
     * Toggles a member on a folder.
     *
     * @param Folder $folder Folder being updated.
     * @param User $member User being added to or removed from the folder.
     * @param User $user Authenticated user managing the members.
     * @param EntityManagerInterface $entityManager Entity manager used to flush the change.
     * @param Request $request Incoming toggle request.
     */
    #[Route('/my-library/folder/{folder}/toggle-member/{member}', name: 'library_folder_toggle_member', methods: ['POST'])]
    public function toggleMember(Folder $folder, User $member, #[CurrentUser] User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($folder->getAuthor() !== $user) {
            $this->addFlash('error', 'Action non autorisée !');
            return $this->redirectToRoute('library_folder_list');
        }

        if (!$this->isCsrfTokenValid('toggle_member' . $folder->getId() . $member->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée (Token CSRF invalide).');
            return $this->redirectToRoute('library_folder_show', ['folder' => $folder->getId()]);
        }

        if ($folder->getMembers()->contains($member)) {
            $folder->removeMember($member);
        } else {
            $folder->addMember($member);
        }

        $entityManager->flush();

        return $this->redirectToRoute('library_folder_show', [
            'folder' => $folder->getId(),
        ]);
    }

    /**
     * Removes the current user from a class.
     *
     * @param Folder $folder Class the user is leaving.
     * @param User $user Authenticated user leaving the class.
     * @param EntityManagerInterface $entityManager Entity manager used to remove the membership.
     * @param Request $request Incoming quit request.
     */
    #[Route('/my-library/folder/{folder}/quit', name: 'library_folder_quit', methods: ['POST'])]
    public function quitFolder(Folder $folder, #[CurrentUser] User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($folder->getAuthor() === $user) {
            $this->addFlash('error', 'Action non autorisée !');
            return $this->redirectToRoute('library_folder_list');
        }

        if (!$this->isCsrfTokenValid('quit_folder_' . $folder->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée (Token CSRF invalide).');
            return $this->redirectToRoute('library_folder_show', ['folder' => $folder->getId()]);
        }

        if ($folder->getMembers()->contains($user)) {
            $folder->removeMember($user);
            $this->addFlash('success', 'Vous avez quitté la classe ' . $folder->getTitle());
        } else {
            return $this->redirectToRoute('explore_class_list');
        }

        $entityManager->flush();

        return $this->redirectToRoute('explore_class_list');
    }
}
