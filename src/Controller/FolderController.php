<?php

namespace App\Controller;

use App\Entity\Folder;
use App\Entity\User;
use App\Form\FolderType;
use App\Repository\FolderRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

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
}
