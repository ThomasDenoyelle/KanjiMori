<?php

namespace App\Controller;

use App\Repository\FolderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class FolderController extends AbstractController
{
    #[Route('/my-library/folder', name: 'library_folder_list')]
    public function myFolder(#[CurrentUser] $user, FolderRepository $folderRepository): Response
    {
        $folderList = $folderRepository->findBy(['author' => $user]);

        return $this->render('folder/library_list.html.twig', [
            'folderList' => $folderList,
        ]);
    }
}
