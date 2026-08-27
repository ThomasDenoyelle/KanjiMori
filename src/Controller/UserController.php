<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AvatarType;
use App\Form\UserType;
use App\Repository\QuizRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
final class UserController extends AbstractController
{
    /**
     * Displays a user's profile and avatar form.
     *
     * @param User|null $user Profile owner to display.
     * @param Request $request Incoming avatar form submission.
     * @param EntityManagerInterface $entityManager Entity manager used to flush avatar changes.
     * @param QuizRepository $quizRepository Repository used to load the user's public quizzes.
     */
    #[Route('/user/{user}/profil', name: 'user_profil')]
    public function profil(?User $user, Request $request, EntityManagerInterface $entityManager, QuizRepository $quizRepository): Response
    {
        if (!$user) {
            $this->addFlash('warning', 'L\'utilisateur n\'existe pas');
            return $this->redirectToRoute('home');
        }

        $avatarForm = $this->createForm(AvatarType::class, $user);
        $avatarForm->handleRequest($request);
        if ($avatarForm->isSubmitted() && $avatarForm->isValid()) {
            if ($this->getUser() !== $user) {
                $this->addFlash('error', 'Vous ne pouvez pas modifier cet avatar.');
                return $this->redirectToRoute('user_profil', ['user' => $user->getId()]);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Votre avatar a bien été mis à jour !');
            return $this->redirectToRoute('user_profil', ['user' => $user->getId()]);
        }

        $publicQuizzes = $quizRepository->findPublicQuizzesWithQuestionsByUser($user);

        return $this->render('user/profil.html.twig', [
            'user' => $user,
            'avatarForm' => $avatarForm,
            'publicQuizzes' => $publicQuizzes,
        ]);
    }

    /**
     * Updates the current user's profile.
     *
     * @param User $user Authenticated user being edited.
     * @param Request $request Incoming profile form submission.
     * @param EntityManagerInterface $entityManager Entity manager used to flush profile changes.
     */
    #[Route('/user/update', name: 'user_update')]
    public function update(#[CurrentUser] User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a bien été modifié');
            return $this->redirectToRoute('user_profil', ['user' => $user->getId()]);
        }

        return $this->render('user/update.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Deletes the current user's account.
     *
     * @param User|null $user Authenticated user requesting the deletion.
     * @param Request $request Incoming delete request.
     * @param EntityManagerInterface $entityManager Entity manager used to remove the account.
     * @param TokenStorageInterface $tokenStorage Token storage used to clear the authentication token.
     */
    #[Route('/user/delete', name: 'user_delete', methods: ['POST'])]
    public function delete(#[CurrentUser] ?User $user, Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $request->getSession()->invalidate();
            $tokenStorage->setToken(null);

            $this->addFlash('success', 'Votre compte et toutes vos données ont bien été supprimés.');
        } else {
            $this->addFlash('error', 'Action non autorisée (Token CSRF invalide).');
        }

        return $this->redirectToRoute('app_login');
    }


    /**
     * Displays the list of users to follow.
     *
     * @param User $user Authenticated user browsing the list.
     * @param UserRepository $userRepository Repository used to load other users.
     */
    #[Route('/user/list', name: 'user_list')]
    public function list(#[CurrentUser] User $user, UserRepository $userRepository): Response
    {
        $users = $userRepository->findAllUserExceptCurrent($user);

        return $this->render('user/list.html.twig', [
            'users' => $users,
        ]);
    }


    /**
     * Toggles the follow state for another user.
     *
     * @param User $user Authenticated user following or unfollowing.
     * @param User $targetUser User to follow or unfollow.
     * @param EntityManagerInterface $entityManager Entity manager used to flush follow changes.
     * @param Request $request Incoming toggle request.
     */
    #[Route('/user/{targetUser}/toggle-follow', name: 'user_toggle_follow', methods: ['POST'])]
    public function toggleFollow(#[CurrentUser] User $user, User $targetUser, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($user === $targetUser) {
            $this->addFlash('error', 'Vous ne pouvez pas suivre vous-même.');
            return $this->redirectToRoute('user_list');
        }

        if (!$this->isCsrfTokenValid('toggle_follow_' . $targetUser->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée (Token CSRF invalide).');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('user_list'));
        }

        if ($user->getFollowing()->contains($targetUser)) {
            $user->removeFollowing($targetUser);
        } else {
            $user->addFollowing($targetUser);
        }

        $entityManager->flush();

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('user_list'));
    }

}
