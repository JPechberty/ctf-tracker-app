<?php

namespace App\Tests\Entity;

use App\Entity\Challenge;
use App\Entity\Team;
use PHPUnit\Framework\TestCase;

class TeamTest extends TestCase
{
    public function testDefaultScoreIsZero(): void
    {
        $team = new Team();

        $this->assertEquals(0, $team->getScore());
    }

    public function testGetRolesReturnsRoleTeam(): void
    {
        $team = new Team();

        $this->assertEquals(['ROLE_TEAM'], $team->getRoles());
    }

    public function testAddPointsIncrementsScore(): void
    {
        $team = new Team();
        $team->addPoints(100);

        $this->assertEquals(100, $team->getScore());

        $team->addPoints(50);
        $this->assertEquals(150, $team->getScore());
    }

    public function testSettersAndGetters(): void
    {
        $team = new Team();
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setStartDate(new \DateTimeImmutable());
        $challenge->setEndDate(new \DateTimeImmutable('+1 day'));

        $team->setName('Team Alpha');
        $team->setUsername('team_alpha');
        $team->setPassword('hashed_password');
        $team->setScore(500);
        $team->setChallenge($challenge);

        $this->assertEquals('Team Alpha', $team->getName());
        $this->assertEquals('team_alpha', $team->getUsername());
        $this->assertEquals('team_alpha', $team->getUserIdentifier());
        $this->assertEquals('hashed_password', $team->getPassword());
        $this->assertEquals(500, $team->getScore());
        $this->assertSame($challenge, $team->getChallenge());
    }

    public function testChallengeRelationship(): void
    {
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setStartDate(new \DateTimeImmutable());
        $challenge->setEndDate(new \DateTimeImmutable('+1 day'));

        $team = new Team();
        $team->setName('Team Beta');
        $team->setUsername('team_beta');

        $challenge->addTeam($team);

        $this->assertCount(1, $challenge->getTeams());
        $this->assertSame($challenge, $team->getChallenge());
    }

    public function testRemoveTeamFromChallenge(): void
    {
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setStartDate(new \DateTimeImmutable());
        $challenge->setEndDate(new \DateTimeImmutable('+1 day'));

        $team = new Team();
        $team->setName('Team Gamma');
        $team->setUsername('team_gamma');

        $challenge->addTeam($team);
        $this->assertCount(1, $challenge->getTeams());

        $challenge->removeTeam($team);
        $this->assertCount(0, $challenge->getTeams());
    }

    public function testToString(): void
    {
        $team = new Team();
        $team->setName('Team Delta');

        $this->assertEquals('Team Delta', (string) $team);
    }

    public function testEraseCredentials(): void
    {
        $team = new Team();
        $team->setPassword('some_password');

        // Should not throw exception
        $team->eraseCredentials();

        // Password should still be set (eraseCredentials doesn't erase password hash)
        $this->assertEquals('some_password', $team->getPassword());
    }
}
