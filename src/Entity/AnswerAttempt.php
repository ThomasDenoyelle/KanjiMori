<?php

namespace App\Entity;

use App\Repository\AnswerAttemptRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnswerAttemptRepository::class)]
class AnswerAttempt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $isCorrect = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $givenKanji = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $givenReading = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $givenTranslation = null;

    #[ORM\ManyToOne(inversedBy: 'answerAttempts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?QuizAttempt $quizAttempt = null;

    #[ORM\ManyToOne(inversedBy: 'answerAttempts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isCorrect(): ?bool
    {
        return $this->isCorrect;
    }

    public function setIsCorrect(bool $isCorrect): static
    {
        $this->isCorrect = $isCorrect;

        return $this;
    }

    public function getGivenKanji(): ?string
    {
        return $this->givenKanji;
    }

    public function setGivenKanji(?string $givenKanji): static
    {
        $this->givenKanji = $givenKanji;

        return $this;
    }

    public function getGivenReading(): ?string
    {
        return $this->givenReading;
    }

    public function setGivenReading(?string $givenReading): static
    {
        $this->givenReading = $givenReading;

        return $this;
    }

    public function getGivenTranslation(): ?string
    {
        return $this->givenTranslation;
    }

    public function setGivenTranslation(?string $givenTranslation): static
    {
        $this->givenTranslation = $givenTranslation;

        return $this;
    }

    public function getQuizAttempt(): ?QuizAttempt
    {
        return $this->quizAttempt;
    }

    public function setQuizAttempt(?QuizAttempt $quizAttempt): static
    {
        $this->quizAttempt = $quizAttempt;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;

        return $this;
    }
}
