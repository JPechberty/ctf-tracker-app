<?php

namespace App\Service;

use App\DTO\ValidationResult;
use App\Entity\Team;
use App\Repository\FlagRepository;
use App\Repository\SubmissionRepository;

class FlagValidationService
{
    public function __construct(
        private FlagRepository $flagRepo,
        private SubmissionRepository $submissionRepo,
    ) {
    }

    public function validateSubmission(Team $team, string $submittedValue): ValidationResult
    {
        $challenge = $team->getChallenge();

        // Control 1: Challenge actif
        if (!$challenge->isActive()) {
            return ValidationResult::failure('Le challenge n\'est pas actif');
        }

        // Control 2: Format valide (PREFIX{...})
        $prefix = $challenge->getPrefix();
        if (!str_starts_with($submittedValue, $prefix . '{') || !str_ends_with($submittedValue, '}')) {
            return ValidationResult::failure('Format de flag invalide');
        }

        // Control 3 & 4: Flag exists and belongs to challenge
        // Flags are stored with full value including prefix (e.g., FLAG{w3b_m4st3r})
        $flag = $this->flagRepo->findOneBy([
            'value' => $submittedValue,
            'challenge' => $challenge,
        ]);

        if (!$flag) {
            return ValidationResult::failure('Flag incorrect');
        }

        // Control 5: Not already validated
        $existingSubmission = $this->submissionRepo->findOneBy([
            'team' => $team,
            'flag' => $flag,
            'success' => true,
        ]);

        if ($existingSubmission) {
            return ValidationResult::failure('Flag deja valide');
        }

        // Control 6: Value correct (already verified by findOneBy with exact value match)

        return ValidationResult::success($flag);
    }
}
