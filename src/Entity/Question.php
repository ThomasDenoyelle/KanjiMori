<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $kanji = null;

    #[ORM\Column(length: 255)]
    private ?string $reading = null;

    #[ORM\Column(length: 255)]
    private ?string $translation = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    /**
     * @var Collection<int, AnswerAttempt>
     */
    #[ORM\OneToMany(targetEntity: AnswerAttempt::class, mappedBy: 'question', orphanRemoval: true)]
    private Collection $answerAttempts;

    public function __construct()
    {
        $this->answerAttempts = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKanji(): ?string
    {
        return $this->kanji;
    }

    public function setKanji(string $kanji): static
    {
        $this->kanji = $kanji;

        return $this;
    }

    public function getReading(): ?string
    {
        return $this->reading;
    }

    public function setReading(string $reading): static
    {
        $this->reading = $reading;

        return $this;
    }

    public function getTranslation(): ?string
    {
        return $this->translation;
    }

    public function setTranslation(string $translation): static
    {
        $this->translation = $translation;

        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }

    /**
     * @return Collection<int, AnswerAttempt>
     */
    public function getAnswerAttempts(): Collection
    {
        return $this->answerAttempts;
    }

    public function addAnswerAttempt(AnswerAttempt $answerAttempt): static
    {
        if (!$this->answerAttempts->contains($answerAttempt)) {
            $this->answerAttempts->add($answerAttempt);
            $answerAttempt->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswerAttempt(AnswerAttempt $answerAttempt): static
    {
        if ($this->answerAttempts->removeElement($answerAttempt)) {
            // set the owning side to null (unless already changed)
            if ($answerAttempt->getQuestion() === $this) {
                $answerAttempt->setQuestion(null);
            }
        }

        return $this;
    }
}
