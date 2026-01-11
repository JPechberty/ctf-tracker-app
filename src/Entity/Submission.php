<?php

namespace App\Entity;

use App\Repository\SubmissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubmissionRepository::class)]
#[ORM\Table(name: 'submission')]
#[ORM\Index(columns: ['team_id', 'flag_id', 'success'], name: 'idx_submission_team_flag_success')]
class Submission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'submissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $team = null;

    #[ORM\ManyToOne(inversedBy: 'submissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Flag $flag = null;

    #[ORM\Column(length: 255)]
    private ?string $submittedValue = null;

    #[ORM\Column]
    private bool $success = false;

    #[ORM\Column]
    private \DateTimeImmutable $submittedAt;

    public function __construct()
    {
        $this->submittedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getFlag(): ?Flag
    {
        return $this->flag;
    }

    public function setFlag(?Flag $flag): static
    {
        $this->flag = $flag;

        return $this;
    }

    public function getSubmittedValue(): ?string
    {
        return $this->submittedValue;
    }

    public function setSubmittedValue(string $submittedValue): static
    {
        $this->submittedValue = $submittedValue;

        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): static
    {
        $this->success = $success;

        return $this;
    }

    public function getSubmittedAt(): \DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(\DateTimeImmutable $submittedAt): static
    {
        $this->submittedAt = $submittedAt;

        return $this;
    }
}
