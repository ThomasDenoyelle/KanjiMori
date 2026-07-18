<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class EmptyQuizAttemptSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ){}

    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        $attempts = $user->getQuizAttempts();

        $hasDeleted = false;

        foreach ($attempts as $attempt) {
            if ($attempt->getAnswerAttempts()->isEmpty()) {
                $this->entityManager->remove($attempt);
                $hasDeleted = true;
            }
        }

        if ($hasDeleted) {
            $this->entityManager->flush();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccessEvent',
        ];
    }
}
