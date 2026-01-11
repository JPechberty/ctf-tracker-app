<?php

namespace App\Tests\Service;

use App\Entity\Challenge;
use App\Entity\Flag;
use App\Entity\Submission;
use App\Entity\Team;
use App\Repository\FlagRepository;
use App\Repository\SubmissionRepository;
use App\Service\FlagValidationService;
use PHPUnit\Framework\TestCase;

class FlagValidationServiceTest extends TestCase
{
    private function createActiveChallenge(): Challenge
    {
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setPrefix('FLAG');
        $challenge->setStartDate(new \DateTimeImmutable('-1 hour'));
        $challenge->setEndDate(new \DateTimeImmutable('+1 hour'));

        return $challenge;
    }

    private function createInactiveChallenge(): Challenge
    {
        $challenge = new Challenge();
        $challenge->setName('Inactive Challenge');
        $challenge->setPrefix('FLAG');
        $challenge->setStartDate(new \DateTimeImmutable('+1 hour'));
        $challenge->setEndDate(new \DateTimeImmutable('+2 hours'));

        return $challenge;
    }

    private function createEndedChallenge(): Challenge
    {
        $challenge = new Challenge();
        $challenge->setName('Ended Challenge');
        $challenge->setPrefix('FLAG');
        $challenge->setStartDate(new \DateTimeImmutable('-2 hours'));
        $challenge->setEndDate(new \DateTimeImmutable('-1 hour'));

        return $challenge;
    }

    private function createTeam(Challenge $challenge): Team
    {
        $team = new Team();
        $team->setName('Test Team');
        $team->setUsername('testteam');
        $team->setPassword('password');
        $team->setChallenge($challenge);

        return $team;
    }

    private function createFlag(Challenge $challenge, string $value = 'FLAG{test}', int $points = 100): Flag
    {
        $flag = new Flag();
        $flag->setName('Test Flag');
        $flag->setValue($value);
        $flag->setPoints($points);
        $flag->setChallenge($challenge);

        return $flag;
    }

    public function testControl1ChallengeNotActive(): void
    {
        $challenge = $this->createInactiveChallenge();
        $team = $this->createTeam($challenge);

        $flagRepo = $this->createMock(FlagRepository::class);
        $submissionRepo = $this->createMock(SubmissionRepository::class);

        $service = new FlagValidationService($flagRepo, $submissionRepo);
        $result = $service->validateSubmission($team, 'FLAG{test}');

        $this->assertFalse($result->success);
        $this->assertEquals('Le challenge n\'est pas actif', $result->message);
        $this->assertEquals(0, $result->points);
        $this->assertNull($result->flag);
    }

    public function testControl1ChallengeEnded(): void
    {
        $challenge = $this->createEndedChallenge();
        $team = $this->createTeam($challenge);

        $flagRepo = $this->createMock(FlagRepository::class);
        $submissionRepo = $this->createMock(SubmissionRepository::class);

        $service = new FlagValidationService($flagRepo, $submissionRepo);
        $result = $service->validateSubmission($team, 'FLAG{test}');

        $this->assertFalse($result->success);
        $this->assertEquals('Le challenge n\'est pas actif', $result->message);
    }

    public function testControl2InvalidFormatMissingPrefix(): void
    {
        $challenge = $this->createActiveChallenge();
        $team = $this->createTeam($challenge);

        $flagRepo = $this->createMock(FlagRepository::class);
        $submissionRepo = $this->createMock(SubmissionRepository::class);

        $service = new FlagValidationService($flagRepo, $submissionRepo);
        $result = $service->validateSubmission($team, 'test');

        $this->assertFalse($result->success);
        $this->assertEquals('Format de flag invalide', $result->message);
    }

    public function testControl2InvalidFormatWrongPrefix(): void
    {
        $challenge = $this->createActiveChallenge();
        $team = $this->createTeam($challenge);

        $flagRepo = $this->createMock(FlagRepository::class);
        $submissionRepo = $this->createMock(SubmissionRepository::class);

        $service = new FlagValidationService($flagRepo, $submissionRepo);
        $result = $service->validateSubmission($team, 'WRONG{test}');

        $this->assertFalse($result->success);
        $this->assertEquals('Format de flag invalide', $result->message);
    }

    public function testControl2InvalidFormatMissingBraces(): void
    {
        $challenge = $this->createActiveChallenge();
        $team = $this->createTeam($challenge);

        $flagRepo = $this->createMock(FlagRepository::class);
        $submissionRepo = $this->createMock(SubmissionRepository::class);

        $service = new FlagValidationService($flagRepo, $submissionRepo);
        $result = $service->validateSubmission($team, 'FLAGtest');

        $this->assertFalse($result->success);
        $this->assertEquals('Format de flag invalide', $result->message);
    }

    public function testControl3FlagNotFound(): void
    {
        $challenge = $this->createActiveChallenge();
        $team = $this->createTeam($challenge);

        $flagRepo = $this->createMock(FlagRepository::class);
        $flagRepo->method('findOneBy')->willReturn(null);

        $submissionRepo = $this->createMock(SubmissionRepository::class);

        $service = new FlagValidationService($flagRepo, $submissionRepo);
        $result = $service->validateSubmission($team, 'FLAG{nonexistent}');

        $this->assertFalse($result->success);
        $this->assertEquals('Flag incorrect', $result->message);
    }

    public function testControl4FlagWrongChallenge(): void
    {
        $challenge = $this->createActiveChallenge();
        $otherChallenge = $this->createActiveChallenge();
        $otherChallenge->setName('Other Challenge');

        $team = $this->createTeam($challenge);
        $flag = $this->createFlag($otherChallenge, 'FLAG{other}');

        // findOneBy with challenge filter returns null because flag belongs to other challenge
        $flagRepo = $this->createMock(FlagRepository::class);
        $flagRepo->method('findOneBy')->willReturn(null);

        $submissionRepo = $this->createMock(SubmissionRepository::class);

        $service = new FlagValidationService($flagRepo, $submissionRepo);
        $result = $service->validateSubmission($team, 'FLAG{other}');

        $this->assertFalse($result->success);
        $this->assertEquals('Flag incorrect', $result->message);
    }

    public function testControl5AlreadyValidated(): void
    {
        $challenge = $this->createActiveChallenge();
        $team = $this->createTeam($challenge);
        $flag = $this->createFlag($challenge, 'FLAG{test}', 100);

        $existingSubmission = new Submission();
        $existingSubmission->setTeam($team);
        $existingSubmission->setFlag($flag);
        $existingSubmission->setSubmittedValue('FLAG{test}');
        $existingSubmission->setSuccess(true);

        $flagRepo = $this->createMock(FlagRepository::class);
        $flagRepo->method('findOneBy')->willReturn($flag);

        $submissionRepo = $this->createMock(SubmissionRepository::class);
        $submissionRepo->method('findOneBy')->willReturn($existingSubmission);

        $service = new FlagValidationService($flagRepo, $submissionRepo);
        $result = $service->validateSubmission($team, 'FLAG{test}');

        $this->assertFalse($result->success);
        $this->assertEquals('Flag deja valide', $result->message);
    }

    public function testControl6ValueMismatch(): void
    {
        $challenge = $this->createActiveChallenge();
        $team = $this->createTeam($challenge);

        // Submitting FLAG{wrong} but no flag matches (case-sensitive)
        $flagRepo = $this->createMock(FlagRepository::class);
        $flagRepo->method('findOneBy')->willReturn(null);

        $submissionRepo = $this->createMock(SubmissionRepository::class);

        $service = new FlagValidationService($flagRepo, $submissionRepo);
        $result = $service->validateSubmission($team, 'FLAG{wrong}');

        $this->assertFalse($result->success);
        $this->assertEquals('Flag incorrect', $result->message);
    }

    public function testSuccessfulValidation(): void
    {
        $challenge = $this->createActiveChallenge();
        $team = $this->createTeam($challenge);
        $flag = $this->createFlag($challenge, 'FLAG{correct}', 250);

        $flagRepo = $this->createMock(FlagRepository::class);
        $flagRepo->method('findOneBy')->willReturn($flag);

        $submissionRepo = $this->createMock(SubmissionRepository::class);
        $submissionRepo->method('findOneBy')->willReturn(null); // No existing submission

        $service = new FlagValidationService($flagRepo, $submissionRepo);
        $result = $service->validateSubmission($team, 'FLAG{correct}');

        $this->assertTrue($result->success);
        $this->assertEquals('Flag valide !', $result->message);
        $this->assertEquals(250, $result->points);
        $this->assertSame($flag, $result->flag);
    }

    public function testSuccessfulValidationWithCustomPrefix(): void
    {
        $challenge = $this->createActiveChallenge();
        $challenge->setPrefix('CTF');
        $team = $this->createTeam($challenge);
        $flag = $this->createFlag($challenge, 'CTF{custom}', 500);

        $flagRepo = $this->createMock(FlagRepository::class);
        $flagRepo->method('findOneBy')->willReturn($flag);

        $submissionRepo = $this->createMock(SubmissionRepository::class);
        $submissionRepo->method('findOneBy')->willReturn(null);

        $service = new FlagValidationService($flagRepo, $submissionRepo);
        $result = $service->validateSubmission($team, 'CTF{custom}');

        $this->assertTrue($result->success);
        $this->assertEquals('Flag valide !', $result->message);
        $this->assertEquals(500, $result->points);
    }
}
