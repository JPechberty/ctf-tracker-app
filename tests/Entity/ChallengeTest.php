<?php

namespace App\Tests\Entity;

use App\Entity\Challenge;
use PHPUnit\Framework\TestCase;

class ChallengeTest extends TestCase
{
    public function testIsActiveReturnsTrueWhenCurrentTimeIsBetweenDates(): void
    {
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setStartDate(new \DateTimeImmutable('-1 hour'));
        $challenge->setEndDate(new \DateTimeImmutable('+1 hour'));

        $this->assertTrue($challenge->isActive());
    }

    public function testIsActiveReturnsFalseWhenChallengeHasNotStarted(): void
    {
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setStartDate(new \DateTimeImmutable('+1 hour'));
        $challenge->setEndDate(new \DateTimeImmutable('+2 hours'));

        $this->assertFalse($challenge->isActive());
    }

    public function testIsActiveReturnsFalseWhenChallengeHasEnded(): void
    {
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setStartDate(new \DateTimeImmutable('-2 hours'));
        $challenge->setEndDate(new \DateTimeImmutable('-1 hour'));

        $this->assertFalse($challenge->isActive());
    }

    public function testIsUpcomingReturnsTrueWhenChallengeHasNotStarted(): void
    {
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setStartDate(new \DateTimeImmutable('+1 hour'));
        $challenge->setEndDate(new \DateTimeImmutable('+2 hours'));

        $this->assertTrue($challenge->isUpcoming());
    }

    public function testIsUpcomingReturnsFalseWhenChallengeHasStarted(): void
    {
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setStartDate(new \DateTimeImmutable('-1 hour'));
        $challenge->setEndDate(new \DateTimeImmutable('+1 hour'));

        $this->assertFalse($challenge->isUpcoming());
    }

    public function testIsEndedReturnsTrueWhenChallengeHasEnded(): void
    {
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setStartDate(new \DateTimeImmutable('-2 hours'));
        $challenge->setEndDate(new \DateTimeImmutable('-1 hour'));

        $this->assertTrue($challenge->isEnded());
    }

    public function testIsEndedReturnsFalseWhenChallengeHasNotEnded(): void
    {
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setStartDate(new \DateTimeImmutable('-1 hour'));
        $challenge->setEndDate(new \DateTimeImmutable('+1 hour'));

        $this->assertFalse($challenge->isEnded());
    }

    public function testDefaultPrefixIsFlag(): void
    {
        $challenge = new Challenge();

        $this->assertEquals('FLAG', $challenge->getPrefix());
    }

    public function testSettersAndGetters(): void
    {
        $challenge = new Challenge();
        $startDate = new \DateTimeImmutable('2026-01-01 10:00:00');
        $endDate = new \DateTimeImmutable('2026-01-01 18:00:00');

        $challenge->setName('CTF 2026');
        $challenge->setDescription('Annual CTF competition');
        $challenge->setPrefix('CTF');
        $challenge->setStartDate($startDate);
        $challenge->setEndDate($endDate);

        $this->assertEquals('CTF 2026', $challenge->getName());
        $this->assertEquals('Annual CTF competition', $challenge->getDescription());
        $this->assertEquals('CTF', $challenge->getPrefix());
        $this->assertEquals($startDate, $challenge->getStartDate());
        $this->assertEquals($endDate, $challenge->getEndDate());
    }
}
