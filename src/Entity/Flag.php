<?php

namespace App\Entity;

use App\Repository\FlagRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FlagRepository::class)]
#[ORM\Table(name: 'flag')]
#[ORM\Index(columns: ['challenge_id', 'value'], name: 'idx_flag_challenge_value')]
class Flag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $value = null;

    #[ORM\Column]
    private int $points = 0;

    #[ORM\ManyToOne(inversedBy: 'flags')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Challenge $challenge = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getChallenge(): ?Challenge
    {
        return $this->challenge;
    }

    public function setChallenge(?Challenge $challenge): static
    {
        $this->challenge = $challenge;

        return $this;
    }
}
