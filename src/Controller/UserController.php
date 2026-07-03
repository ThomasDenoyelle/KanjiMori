<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
final class UserController extends AbstractController
{
    #[Route('/user/{user}/profil', name: 'user_profil')]
    public function profil(?User $user): Response
    {
        if (!$user) {
            $this->addFlash('warning', 'L\'utilisateur n\'existe pas');
            return $this->redirectToRoute('home');
        }
        return $this->render('user/profil.html.twig', [
            'user' => $user,
        ]);
    }




}
