<?php

namespace App\DataFixtures;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('user@user.com');
        $user->setFirstName('Jeanne-Marie');
        $user->setLastName('Prédine Kraljic');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setIsVerified(true);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'caca'
        );

        $user->setPassword($hashedPassword);
        $manager->persist($user);

        $quizElements = new Quiz();
        $quizElements->setTitle('Les éléments de base (N5)');
        $quizElements->setAuthor($user);
        $manager->persist($quizElements);

        $questionsElements = [
            ['kanji' => '水', 'reading' => 'みず', 'translation' => 'eau'],
            ['kanji' => '火', 'reading' => 'ひ', 'translation' => 'feu'],
            ['kanji' => '木', 'reading' => 'き', 'translation' => 'arbre'],
            ['kanji' => '日', 'reading' => 'ひ', 'translation' => 'soleil'],
            ['kanji' => '月', 'reading' => 'つき', 'translation' => 'lune'],
        ];

        foreach ($questionsElements as $data) {
            $question = new Question();
            $question->setKanji($data['kanji']);
            $question->setReading($data['reading']);
            $question->setTranslation($data['translation']);
            $question->setQuiz($quizElements);

            $manager->persist($question);
        }

        $quizTime = new Quiz();
        $quizTime->setTitle('Temps et jours (N5)');
        $quizTime->setAuthor($user);
        $manager->persist($quizTime);

        $questionsTime = [
            ['kanji' => '今日', 'reading' => 'きょう', 'translation' => 'aujourd\'hui'],
            ['kanji' => '明日', 'reading' => 'あした', 'translation' => 'demain'],
            ['kanji' => '昨日', 'reading' => 'きのう', 'translation' => 'hier'],
        ];

        foreach ($questionsTime as $data) {
            $question = new Question();
            $question->setKanji($data['kanji']);
            $question->setReading($data['reading']);
            $question->setTranslation($data['translation']);
            $question->setQuiz($quizTime);

            $manager->persist($question);
        }

        $manager->flush();
    }
}
