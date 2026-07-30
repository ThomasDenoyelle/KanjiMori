<?php

namespace App\DataFixtures;

use App\Entity\Folder;
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
        $user->setPassword($this->passwordHasher->hashPassword($user, 'caca'));
        $manager->persist($user);

        $prof = new User();
        $prof->setEmail('nishio@gmail.com');
        $prof->setFirstName('Nishio');
        $prof->setLastName('Sensei');
        $prof->setRoles(['ROLE_USER']);
        $prof->setIsVerified(true);
        $prof->setPassword($this->passwordHasher->hashPassword($prof, 'nishio123'));
        $manager->persist($prof);

        $aelis = new User();
        $aelis->setEmail('aelis@gmail.com');
        $aelis->setFirstName('Aélis');
        $aelis->setLastName('Kanji');
        $aelis->setRoles(['ROLE_USER']);
        $aelis->setIsVerified(true);
        $aelis->setPassword($this->passwordHasher->hashPassword($aelis, 'password123'));
        $manager->persist($aelis);

        $magenta = new User();
        $magenta->setEmail('magenta@gmail.com');
        $magenta->setFirstName('Magenta');
        $magenta->setLastName('Hiragana');
        $magenta->setRoles(['ROLE_USER']);
        $magenta->setIsVerified(true);
        $magenta->setPassword($this->passwordHasher->hashPassword($magenta, 'password123'));
        $manager->persist($magenta);


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



        $quizPublic1 = new Quiz();
        $quizPublic1->setTitle('Salutations de tous les jours');
        $quizPublic1->setAuthor($prof);
        $quizPublic1->setIsPublic(true); // Quiz public
        $manager->persist($quizPublic1);

        $questionsGreetings = [
            ['kanji' => '挨拶', 'reading' => 'あいさつ', 'translation' => 'salutations'],
            ['kanji' => 'おはよう', 'reading' => 'おはよう', 'translation' => 'bonjour (matin)'],
            ['kanji' => 'こんばんは', 'reading' => 'こんばんは', 'translation' => 'bonsoir'],
        ];

        foreach ($questionsGreetings as $data) {
            $question = new Question();
            $question->setKanji($data['kanji']);
            $question->setReading($data['reading']);
            $question->setTranslation($data['translation']);
            $question->setQuiz($quizPublic1);
            $manager->persist($question);
        }

        $quizPublic2 = new Quiz();
        $quizPublic2->setTitle('Les couleurs (N5)');
        $quizPublic2->setAuthor($prof);
        $quizPublic2->setIsPublic(true);
        $manager->persist($quizPublic2);

        $questionsColors = [
            ['kanji' => '赤', 'reading' => 'あか', 'translation' => 'rouge'],
            ['kanji' => '青', 'reading' => 'あお', 'translation' => 'bleu'],
            ['kanji' => '白', 'reading' => 'しろ', 'translation' => 'blanc'],
            ['kanji' => '黒', 'reading' => 'くろ', 'translation' => 'noir'],
        ];

        foreach ($questionsColors as $data) {
            $question = new Question();
            $question->setKanji($data['kanji']);
            $question->setReading($data['reading']);
            $question->setTranslation($data['translation']);
            $question->setQuiz($quizPublic2);
            $manager->persist($question);
        }

        $folderPrivate = new Folder();
        $folderPrivate->setTitle('Mes révisions N5');
        $folderPrivate->setDescription('Dossier personnel pour organiser mes propres quiz.');
        $folderPrivate->setAuthor($user);
        $folderPrivate->setIsPublic(false);
        $folderPrivate->addQuiz($quizElements);
        $folderPrivate->addQuiz($quizTime);
        $manager->persist($folderPrivate);

        $folderClass = new Folder();
        $folderClass->setTitle('Classe Japonais Débutant A1');
        $folderClass->setDescription('Dossier contenant les exercices hebdomadaires.');
        $folderClass->setAuthor($prof);
        $folderClass->setIsPublic(true);
        $folderClass->addQuiz($quizPublic1);
        $folderClass->addQuiz($quizPublic2);
        $folderClass->addMember($user);
        $folderClass->addMember($magenta);
        $manager->persist($folderClass);

        $folderStudy = new Folder();
        $folderStudy->setTitle('Groupe d\'étude Kanji');
        $folderStudy->setDescription('Dossier partagé avec Thomas pour réviser ensemble.');
        $folderStudy->setAuthor($user);
        $folderStudy->setIsPublic(true);
        $folderStudy->addQuiz($quizElements);
        $folderStudy->addMember($aelis);
        $manager->persist($folderStudy);


        $manager->flush();
    }
}
