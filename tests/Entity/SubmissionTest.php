<?php

namespace App\Tests\Entity;

use App\Entity\Challenge;
use App\Entity\Flag;
use App\Entity\Submission;
use App\Entity\Team;
use PHPUnit\Framework\TestCase;

class SubmissionTest extends TestCase
{
    private function createTestChallenge(): Challenge
    {
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setStartDate(new \DateTimeImmutable());
        $challenge->setEndDate(new \DateTimeImmutable('+1 day'));

        return $challenge;
    }

    private function createTestTeam(Challenge $challenge): Team
    {
        $team = new Team();
        $team->setName('Test Team');
        $team->setUsername('testteam');
        $team->setPassword('password');
        $team->setChallenge($challenge);

        return $team;
    }

    private function createTestFlag(Challenge $challenge): Flag
    {
        $flag = new Flag();
        $flag->setName('Test Flag');
        $flag->setValue('FLAG{test}');
        $flag->setPoints(100);
        $flag->setChallenge($challenge);

        return $flag;
    }

    public function testSubmittedAtIsAutoSetInConstructor(): void
    {
        $before = new \DateTimeImmutable();
        $submission = new Submission();
        $after = new \DateTimeImmutable();

        $this->assertInstanceOf(\DateTimeImmutable::class, $submission->getSubmittedAt());
        $this->assertGreaterThanOrEqual($before, $submission->getSubmittedAt());
        $this->assertLessThanOrEqual($after, $submission->getSubmittedAt());
    }

    public function testDefaultSuccessIsFalse(): void
    {
        $submission = new Submission();

        $this->assertFalse($submission->isSuccess());
    }

    public function testSettersAndGetters(): void
    {
        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge);
        $flag = $this->createTestFlag($challenge);

        $submission = new Submission();
        $submission->setTeam($team);
        $submission->setFlag($flag);
        $submission->setSubmittedValue('FLAG{test}');
        $submission->setSuccess(true);

        $this->assertSame($team, $submission->getTeam());
        $this->assertSame($flag, $submission->getFlag());
        $this->assertEquals('FLAG{test}', $submission->getSubmittedValue());
        $this->assertTrue($submission->isSuccess());
    }

    public function testTeamRelationship(): void
    {
        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge);
        $flag = $this->createTestFlag($challenge);

        $submission = new Submission();
        $submission->setTeam($team);
        $submission->setFlag($flag);
        $submission->setSubmittedValue('test');

        $this->assertSame($team, $submission->getTeam());
    }

    public function testFlagRelationship(): void
    {
        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge);
        $flag = $this->createTestFlag($challenge);

        $submission = new Submission();
        $submission->setTeam($team);
        $submission->setFlag($flag);
        $submission->setSubmittedValue('test');

        $this->assertSame($flag, $submission->getFlag());
    }

    public function testSubmittedAtCanBeOverridden(): void
    {
        $submission = new Submission();
        $customDate = new \DateTimeImmutable('2025-01-01 12:00:00');
        $submission->setSubmittedAt($customDate);

        $this->assertEquals($customDate, $submission->getSubmittedAt());
    }
}
