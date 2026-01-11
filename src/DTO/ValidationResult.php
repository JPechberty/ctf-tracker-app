<?php

namespace App\DTO;

use App\Entity\Flag;

readonly class ValidationResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public int $points = 0,
        public ?Flag $flag = null,
    ) {
    }

    public static function success(Flag $flag): self
    {
        return new self(true, 'Flag valide !', $flag->getPoints(), $flag);
    }

    public static function failure(string $message): self
    {
        return new self(false, $message);
    }
}
