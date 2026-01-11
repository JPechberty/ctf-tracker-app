<?php

namespace App\Tests\Controller;

use App\Entity\Challenge;
use App\Entity\Flag;
use App\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LeaderboardControllerTest extends WebTestCase
{
    private function createTestChallenge(string $name = null): Challenge
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $challenge = new Challenge();
        $challenge->setName($name ?? 'Leaderboard Test Challenge ' . uniqid());
        $challenge->setPrefix('LB');
        $challenge->setStartDate(new \DateTimeImmutable());
        $challenge->setEndDate(new \DateTimeImmutable('+1 day'));

        $entityManager->persist($challenge);
        $entityManager->flush();

        return $challenge;
    }

    private function createTestTeam(Challenge $challenge, int $score = 0, ?string $name = null): Team
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $team = new Team();
        $team->setName($name ?? 'LB Team ' . uniqid());
        $team->setUsername('lbteam' . uniqid());
        $team->setChallenge($challenge);
        $team->setScore($score);

        $hasher = $container->get(UserPasswordHasherInterface::class);
        $team->setPassword($hasher->hashPassword($team, 'password'));

        $entityManager->persist($team);
        $entityManager->flush();

        return $team;
    }

    private function createTestFlag(Challenge $challenge, int $points = 100): Flag
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $flag = new Flag();
        $flag->setName('Flag ' . uniqid());
        $flag->setValue('LB{' . uniqid() . '}');
        $flag->setPoints($points);

        // Use addFlag to properly sync bidirectional relationship
        $challenge->addFlag($flag);

        $entityManager->persist($flag);
        $entityManager->flush();

        return $flag;
    }

    public function testLeaderboardIsPublicNoAuthRequired(): void
    {
        $client = static::createClient();
        $this->createTestChallenge();

        $client->request('GET', '/leaderboard');

        $this->assertResponseIsSuccessful();
    }

    public function testLeaderboardDisplaysChallengeName(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge('CTF Hackathon 2025');

        $client->request('GET', '/leaderboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'CTF Hackathon 2025');
    }

    public function testLeaderboardHasTimer(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();

        $crawler = $client->request('GET', '/leaderboard');

        $this->assertResponseIsSuccessful();

        $timer = $crawler->filter('[data-controller="timer"]');
        $this->assertCount(1, $timer);
        $this->assertNotEmpty($timer->attr('data-timer-end-time-value'));
        $this->assertNotEmpty($timer->attr('data-timer-start-time-value'));
    }

    public function testLeaderboardListsTeamsByScoreDescending(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();

        $this->createTestTeam($challenge, 100, 'Team Low');
        $this->createTestTeam($challenge, 300, 'Team High');
        $this->createTestTeam($challenge, 200, 'Team Mid');

        $crawler = $client->request('GET', '/leaderboard');

        $this->assertResponseIsSuccessful();

        $rows = $crawler->filter('tbody tr');
        $this->assertCount(3, $rows);

        // First row should be Team High (300 pts)
        $this->assertStringContainsString('Team High', $rows->eq(0)->text());
        // Second should be Team Mid (200 pts)
        $this->assertStringContainsString('Team Mid', $rows->eq(1)->text());
        // Third should be Team Low (100 pts)
        $this->assertStringContainsString('Team Low', $rows->eq(2)->text());
    }

    public function testLeaderboardShowsMedalIconsForTopThree(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();

        $this->createTestTeam($challenge, 300, 'First');
        $this->createTestTeam($challenge, 200, 'Second');
        $this->createTestTeam($challenge, 100, 'Third');
        $this->createTestTeam($challenge, 50, 'Fourth');

        $crawler = $client->request('GET', '/leaderboard');

        $this->assertResponseIsSuccessful();

        $rows = $crawler->filter('tbody tr');

        $this->assertStringContainsString("\u{1F947}", $rows->eq(0)->text()); // Gold medal
        $this->assertStringContainsString("\u{1F948}", $rows->eq(1)->text()); // Silver medal
        $this->assertStringContainsString("\u{1F949}", $rows->eq(2)->text()); // Bronze medal
        $this->assertStringContainsString('#4', $rows->eq(3)->text()); // Fourth place shows #4
    }

    public function testLeaderboardShowsTeamNameAndScore(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();

        $this->createTestTeam($challenge, 250, 'Test Team Alpha');

        $crawler = $client->request('GET', '/leaderboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('tbody tr', 'Test Team Alpha');
        $this->assertSelectorTextContains('tbody tr', '250 pts');
    }

    public function testLeaderboardTiedTeamsShareRank(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();

        $this->createTestTeam($challenge, 300, 'Team A');
        $this->createTestTeam($challenge, 200, 'Team B');
        $this->createTestTeam($challenge, 200, 'Team C');
        $this->createTestTeam($challenge, 100, 'Team D');

        $crawler = $client->request('GET', '/leaderboard');

        $this->assertResponseIsSuccessful();

        $rows = $crawler->filter('tbody tr');

        // First: gold medal (rank 1)
        $this->assertStringContainsString("\u{1F947}", $rows->eq(0)->text());

        // Second and Third: silver medal (both rank 2)
        $this->assertStringContainsString("\u{1F948}", $rows->eq(1)->text());
        $this->assertStringContainsString("\u{1F948}", $rows->eq(2)->text());

        // Fourth: should be #4 (rank 4, skipping 3)
        $this->assertStringContainsString('#4', $rows->eq(3)->text());
    }

    public function testLeaderboardShowsTotalFlagsCount(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();

        $this->createTestFlag($challenge, 100);
        $this->createTestFlag($challenge, 150);
        $this->createTestFlag($challenge, 200);

        $client->request('GET', '/leaderboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', '3 flags disponibles');
    }

    public function testLeaderboardHasActualiserButton(): void
    {
        $client = static::createClient();
        $this->createTestChallenge();

        $crawler = $client->request('GET', '/leaderboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a[href="/leaderboard"]');
        $this->assertSelectorTextContains('a[href="/leaderboard"].btn', 'Actualiser');
    }

    public function testLeaderboardShowsEmptyMessageWhenNoTeams(): void
    {
        $client = static::createClient();
        $this->createTestChallenge();

        $client->request('GET', '/leaderboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('tbody', 'Aucune equipe inscrite');
    }

    public function testLeaderboardThrows404WhenNoChallenge(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        // Clear all challenges
        $em->createQuery('DELETE FROM App\Entity\Submission')->execute();
        $em->createQuery('DELETE FROM App\Entity\Team')->execute();
        $em->createQuery('DELETE FROM App\Entity\Flag')->execute();
        $em->createQuery('DELETE FROM App\Entity\Challenge')->execute();

        $client->request('GET', '/leaderboard');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testLeaderboardHasResponsiveTable(): void
    {
        $client = static::createClient();
        $this->createTestChallenge();

        $crawler = $client->request('GET', '/leaderboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table.table');
    }

    public function testLeaderboardHasLeaderboardTableClass(): void
    {
        $client = static::createClient();
        $this->createTestChallenge();

        $crawler = $client->request('GET', '/leaderboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table.leaderboard-table');
    }
}
