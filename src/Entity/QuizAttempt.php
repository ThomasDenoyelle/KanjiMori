<?php

namespace App\Entity;

use App\Repository\QuizAttemptRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizAttemptRepository::class)]
#[ORM\HasLifecycleCallbacks]
class QuizAttempt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $score = null;

    #[ORM\Column]
    private ?int $maxScore = null;

    #[ORM\Column(length: 255)]
    private ?string $mode = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'quizAttempts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'quizAttempts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    /**
     * @var Collection<int, AnswerAttempt>
     */
    #[ORM\OneToMany(targetEntity: AnswerAttempt::class, mappedBy: 'quizAttempt', orphanRemoval: true)]
    private Collection $answerAttempts;

    #[ORM\Column]
    private array $questionOrder = [];

    public function __construct()
    {
        $this->answerAttempts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getMaxScore(): ?int
    {
        return $this->maxScore;
    }

    public function setMaxScore(int $maxScore): static
    {
        $this->maxScore = $maxScore;

        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(string $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

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

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
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
            $answerAttempt->setQuizAttempt($this);
        }

        return $this;
    }

    public function removeAnswerAttempt(AnswerAttempt $answerAttempt): static
    {
        if ($this->answerAttempts->removeElement($answerAttempt)) {
            // set the owning side to null (unless already changed)
            if ($answerAttempt->getQuizAttempt() === $this) {
                $answerAttempt->setQuizAttempt(null);
            }
        }

        return $this;
    }

    public function getQuestionOrder(): array
    {
        return $this->questionOrder;
    }

    public function setQuestionOrder(array $questionOrder): static
    {
        $this->questionOrder = $questionOrder;

        return $this;
    }
}
