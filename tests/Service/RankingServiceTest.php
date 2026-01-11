<?php

namespace App\Tests\Service;

use App\Entity\Challenge;
use App\Entity\Team;
use App\Repository\TeamRepository;
use App\Service\RankingService;
use PHPUnit\Framework\TestCase;

class RankingServiceTest extends TestCase
{
    private function createChallenge(): Challenge
    {
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setStartDate(new \DateTimeImmutable());
        $challenge->setEndDate(new \DateTimeImmutable('+1 day'));

        return $challenge;
    }

    private function createTeam(int $id, string $name, int $score, Challenge $challenge): Team
    {
        $team = new Team();
        // Use reflection to set the ID since it's normally auto-generated
        $reflection = new \ReflectionClass($team);
        $property = $reflection->getProperty('id');
        $property->setValue($team, $id);

        $team->setName($name);
        $team->setUsername(strtolower(str_replace(' ', '_', $name)));
        $team->setScore($score);
        $team->setChallenge($challenge);

        return $team;
    }

    public function testGetTeamRankWithSingleTeam(): void
    {
        $challenge = $this->createChallenge();
        $team = $this->createTeam(1, 'Team Alpha', 100, $challenge);

        $teamRepo = $this->createMock(TeamRepository::class);
        $teamRepo->method('findByChallengeSortedByScore')
            ->willReturn([$team]);

        $service = new RankingService($teamRepo);

        $this->assertEquals(1, $service->getTeamRank($team));
    }

    public function testGetTeamRankWithMultipleTeams(): void
    {
        $challenge = $this->createChallenge();
        $team1 = $this->createTeam(1, 'Team Alpha', 300, $challenge);
        $team2 = $this->createTeam(2, 'Team Beta', 200, $challenge);
        $team3 = $this->createTeam(3, 'Team Gamma', 100, $challenge);

        $teams = [$team1, $team2, $team3];

        $teamRepo = $this->createMock(TeamRepository::class);
        $teamRepo->method('findByChallengeSortedByScore')
            ->willReturn($teams);

        $service = new RankingService($teamRepo);

        $this->assertEquals(1, $service->getTeamRank($team1));
        $this->assertEquals(2, $service->getTeamRank($team2));
        $this->assertEquals(3, $service->getTeamRank($team3));
    }

    public function testGetTeamRankWithTies(): void
    {
        $challenge = $this->createChallenge();
        $team1 = $this->createTeam(1, 'Team Alpha', 300, $challenge);
        $team2 = $this->createTeam(2, 'Team Beta', 200, $challenge);
        $team3 = $this->createTeam(3, 'Team Gamma', 200, $challenge);
        $team4 = $this->createTeam(4, 'Team Delta', 100, $challenge);

        $teams = [$team1, $team2, $team3, $team4];

        $teamRepo = $this->createMock(TeamRepository::class);
        $teamRepo->method('findByChallengeSortedByScore')
            ->willReturn($teams);

        $service = new RankingService($teamRepo);

        $this->assertEquals(1, $service->getTeamRank($team1)); // 300 pts = rank 1
        $this->assertEquals(2, $service->getTeamRank($team2)); // 200 pts = rank 2 (tied)
        $this->assertEquals(2, $service->getTeamRank($team3)); // 200 pts = rank 2 (tied)
        $this->assertEquals(4, $service->getTeamRank($team4)); // 100 pts = rank 4 (skips 3)
    }

    public function testGetTeamRankWithAllTied(): void
    {
        $challenge = $this->createChallenge();
        $team1 = $this->createTeam(1, 'Team Alpha', 100, $challenge);
        $team2 = $this->createTeam(2, 'Team Beta', 100, $challenge);
        $team3 = $this->createTeam(3, 'Team Gamma', 100, $challenge);

        $teams = [$team1, $team2, $team3];

        $teamRepo = $this->createMock(TeamRepository::class);
        $teamRepo->method('findByChallengeSortedByScore')
            ->willReturn($teams);

        $service = new RankingService($teamRepo);

        $this->assertEquals(1, $service->getTeamRank($team1));
        $this->assertEquals(1, $service->getTeamRank($team2));
        $this->assertEquals(1, $service->getTeamRank($team3));
    }

    public function testGetTeamRankWithZeroScores(): void
    {
        $challenge = $this->createChallenge();
        $team1 = $this->createTeam(1, 'Team Alpha', 100, $challenge);
        $team2 = $this->createTeam(2, 'Team Beta', 0, $challenge);
        $team3 = $this->createTeam(3, 'Team Gamma', 0, $challenge);

        $teams = [$team1, $team2, $team3];

        $teamRepo = $this->createMock(TeamRepository::class);
        $teamRepo->method('findByChallengeSortedByScore')
            ->willReturn($teams);

        $service = new RankingService($teamRepo);

        $this->assertEquals(1, $service->getTeamRank($team1));
        $this->assertEquals(2, $service->getTeamRank($team2));
        $this->assertEquals(2, $service->getTeamRank($team3));
    }
}
