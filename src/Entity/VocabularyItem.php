<?php

namespace App\Entity;

use App\Repository\VocabularyItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VocabularyItemRepository::class)]
class VocabularyItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $kanji = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reading = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $translation = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?VocabularySheet $sheet = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKanji(): ?string
    {
        return $this->kanji;
    }

    public function setKanji(?string $kanji): static
    {
        $this->kanji = $kanji;

        return $this;
    }

    public function getReading(): ?string
    {
        return $this->reading;
    }

    public function setReading(?string $reading): static
    {
        $this->reading = $reading;

        return $this;
    }

    public function getTranslation(): ?string
    {
        return $this->translation;
    }

    public function setTranslation(?string $translation): static
    {
        $this->translation = $translation;

        return $this;
    }

    public function getSheet(): ?VocabularySheet
    {
        return $this->sheet;
    }

    public function setSheet(?VocabularySheet $sheet): static
    {
        $this->sheet = $sheet;

        return $this;
    }
}
