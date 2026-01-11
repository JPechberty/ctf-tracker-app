<?php

namespace App\Tests\Controller;

use App\Entity\Challenge;
use App\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DashboardControllerTest extends WebTestCase
{
    private function createTestChallenge(): Challenge
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $challenge = new Challenge();
        $challenge->setName('Dashboard Test Challenge ' . uniqid());
        $challenge->setPrefix('DASH');
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
        $team->setName($name ?? 'Dashboard Team ' . uniqid());
        $team->setUsername('dashteam' . uniqid());
        $team->setChallenge($challenge);
        $team->setScore($score);

        $hasher = $container->get(UserPasswordHasherInterface::class);
        $team->setPassword($hasher->hashPassword($team, 'password'));

        $entityManager->persist($team);
        $entityManager->flush();

        return $team;
    }

    public function testDashboardRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard');

        $this->assertResponseRedirects('/login');
    }

    public function testDashboardDisplaysTeamName(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge, 0, 'Test Team Display');

        $client->loginUser($team, 'main');
        $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('nav', 'Test Team Display');
    }

    public function testDashboardDisplaysScore(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge, 450);

        $client->loginUser($team, 'main');
        $crawler = $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.card-body', '450 pts');
    }

    public function testDashboardDisplaysRank(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();

        // Create teams with different scores
        $this->createTestTeam($challenge, 500);
        $this->createTestTeam($challenge, 300);
        $team = $this->createTestTeam($challenge, 200); // This team should be rank 3

        $client->loginUser($team, 'main');
        $crawler = $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();
        // Get the second card-body which contains the rank
        $rankCard = $crawler->filter('.card-body')->eq(1);
        $this->assertStringContainsString('#3', $rankCard->text());
    }

    public function testDashboardDisplaysRankWithTies(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();

        // Create teams with same scores (tied)
        $this->createTestTeam($challenge, 500);
        $team = $this->createTestTeam($challenge, 500); // Should also be rank 1 (tied)

        $client->loginUser($team, 'main');
        $crawler = $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();
        // Get the second card-body which contains the rank
        $rankCard = $crawler->filter('.card-body')->eq(1);
        $this->assertStringContainsString('#1', $rankCard->text());
    }

    public function testDashboardHasLogoutButton(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge);

        $client->loginUser($team, 'main');
        $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a[href="/logout"]');
        $this->assertSelectorTextContains('a.btn-outline-danger', 'Deconnexion');
    }

    public function testDashboardLayoutHasScoreAndRankCards(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge, 100);

        $client->loginUser($team, 'main');
        $crawler = $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();

        // Check for score card
        $this->assertSelectorTextContains('.card-title', 'VOTRE SCORE');

        // Check for rank card
        $cards = $crawler->filter('.card-title');
        $cardTexts = [];
        foreach ($cards as $card) {
            $cardTexts[] = $card->textContent;
        }
        $this->assertContains('VOTRE RANG', $cardTexts);
    }

    public function testLogoutButtonWorks(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge);

        $client->loginUser($team, 'main');
        $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();

        // Click logout
        $client->request('GET', '/logout');
        $this->assertResponseRedirects();

        // Try accessing dashboard again - should redirect to login
        $client->followRedirect();
        $client->request('GET', '/dashboard');
        $this->assertResponseRedirects('/login');
    }

    // Story 2.4 Tests

    public function testDashboardHasTimerElement(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge);

        $client->loginUser($team, 'main');
        $crawler = $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();

        // Check timer element exists with Stimulus controller
        $timer = $crawler->filter('[data-controller="timer"]');
        $this->assertCount(1, $timer);

        // Check timer has required data attributes
        $this->assertNotEmpty($timer->attr('data-timer-end-time-value'));
        $this->assertNotEmpty($timer->attr('data-timer-start-time-value'));
    }

    public function testDashboardTimerHasCorrectTimestamps(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $startDate = new \DateTimeImmutable('+1 hour');
        $endDate = new \DateTimeImmutable('+2 hours');

        $challenge = new Challenge();
        $challenge->setName('Timer Test Challenge ' . uniqid());
        $challenge->setPrefix('TIME');
        $challenge->setStartDate($startDate);
        $challenge->setEndDate($endDate);

        $entityManager->persist($challenge);
        $entityManager->flush();

        $team = $this->createTestTeam($challenge);

        $client->loginUser($team, 'main');
        $crawler = $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();

        $timer = $crawler->filter('[data-controller="timer"]');
        $this->assertEquals($endDate->getTimestamp(), $timer->attr('data-timer-end-time-value'));
        $this->assertEquals($startDate->getTimestamp(), $timer->attr('data-timer-start-time-value'));
    }

    public function testDashboardShowsEmptyFlagsMessage(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge);

        $client->loginUser($team, 'main');
        $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.text-muted', 'Aucun flag valide pour le moment');
    }

    public function testDashboardShowsFlagsCount(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge);

        $client->loginUser($team, 'main');
        $crawler = $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();

        // Check FLAGS VALIDES section exists with count format
        $flagsCard = $crawler->filter('.card-title:contains("FLAGS VALIDES")');
        $this->assertCount(1, $flagsCard);
        $this->assertStringContainsString('(0/', $flagsCard->text());
    }

    public function testDashboardHasLeaderboardLink(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge);

        $client->loginUser($team, 'main');
        $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a[href="/leaderboard"]');
        $this->assertSelectorTextContains('a[href="/leaderboard"]', 'Voir le leaderboard');
    }

    public function testLeaderboardPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/leaderboard');

        $this->assertResponseIsSuccessful();
    }

    public function testLeaderboardHasBackLink(): void
    {
        $client = static::createClient();
        $client->request('GET', '/leaderboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a[href="/dashboard"]');
    }
}
