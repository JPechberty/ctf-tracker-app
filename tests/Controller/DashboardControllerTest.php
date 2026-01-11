<?php

namespace App\Tests\Controller;

use App\Entity\Challenge;
use App\Entity\Flag;
use App\Entity\Submission;
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
        $this->assertSelectorTextContains('.empty-flags-message', 'Aucun flag valide pour le moment');
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

    // Story 3.3 Tests - Flag Submission Form

    public function testDashboardHasSubmissionForm(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge);

        $client->loginUser($team, 'main');
        $crawler = $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();

        // Check that SOUMETTRE UN FLAG card exists
        $cardTitles = $crawler->filter('.card-title');
        $found = false;
        foreach ($cardTitles as $title) {
            if (str_contains($title->textContent, 'SOUMETTRE UN FLAG')) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'SOUMETTRE UN FLAG card not found');
        $this->assertSelectorExists('form input[name="flag"]');
        $this->assertSelectorExists('form button[type="submit"]');
    }

    public function testDashboardShowsFormatHint(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge);

        $client->loginUser($team, 'main');
        $crawler = $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('small.text-muted', 'Format attendu: DASH{...}');
    }

    public function testSuccessfulFlagSubmission(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        $challenge = $this->createTestChallenge();

        // Create a flag for the challenge
        $flag = new Flag();
        $flag->setName('Test Flag');
        $flag->setValue('DASH{test123}');
        $flag->setPoints(100);
        $flag->setChallenge($challenge);
        $em->persist($flag);
        $em->flush();

        $team = $this->createTestTeam($challenge, 0);
        $teamId = $team->getId();

        $client->loginUser($team, 'main');
        $crawler = $client->request('GET', '/dashboard');

        // Submit the correct flag
        $form = $crawler->selectButton('Valider')->form();
        $form['flag'] = 'DASH{test123}';
        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-success', 'points');

        // Verify team score was updated by checking the page content
        $this->assertSelectorTextContains('.card-body', '100 pts');

        // Verify input is cleared on success
        $this->assertSelectorExists('input[name="flag"][value=""]');
    }

    public function testFailedFlagSubmission(): void
    {
        $client = static::createClient();
        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge, 0);

        $client->loginUser($team, 'main');
        $crawler = $client->request('GET', '/dashboard');

        // Submit an incorrect flag
        $form = $crawler->selectButton('Valider')->form();
        $form['flag'] = 'DASH{wrong}';
        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert-danger');

        // Verify input retains the submitted value
        $this->assertSelectorExists('input[name="flag"][value="DASH{wrong}"]');
    }

    public function testSubmissionIsPersisted(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        $challenge = $this->createTestChallenge();
        $team = $this->createTestTeam($challenge);
        $teamId = $team->getId();
        $uniqueValue = 'DASH{persist_' . uniqid() . '}';

        $client->loginUser($team, 'main');
        $crawler = $client->request('GET', '/dashboard');

        // Submit a flag with unique value
        $form = $crawler->selectButton('Valider')->form();
        $form['flag'] = $uniqueValue;
        $client->submit($form);

        $this->assertResponseIsSuccessful();

        // Get fresh entity manager after the request
        $em->clear();
        $submissionRepo = $em->getRepository(Submission::class);
        $submission = $submissionRepo->findOneBy(['submittedValue' => $uniqueValue]);
        $this->assertNotNull($submission);
        $this->assertEquals($teamId, $submission->getTeam()->getId());
    }

    public function testValidatedFlagAppearsInList(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        $challenge = $this->createTestChallenge();

        // Create a flag
        $flag = new Flag();
        $flag->setName('Visible Flag');
        $flag->setValue('DASH{visible}');
        $flag->setPoints(50);
        $flag->setChallenge($challenge);
        $em->persist($flag);
        $em->flush();

        $team = $this->createTestTeam($challenge, 0);

        $client->loginUser($team, 'main');
        $crawler = $client->request('GET', '/dashboard');

        // Submit the correct flag
        $form = $crawler->selectButton('Valider')->form();
        $form['flag'] = 'DASH{visible}';
        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.list-group-item', 'Visible Flag');
        $this->assertSelectorTextContains('.badge.bg-success', '+50 pts');
    }

    public function testScoreCardUpdatesAfterSubmission(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        $challenge = $this->createTestChallenge();

        // Create a flag
        $flag = new Flag();
        $flag->setName('Score Flag');
        $flag->setValue('DASH{score}');
        $flag->setPoints(200);
        $flag->setChallenge($challenge);
        $em->persist($flag);
        $em->flush();

        $team = $this->createTestTeam($challenge, 100);

        $client->loginUser($team, 'main');
        $crawler = $client->request('GET', '/dashboard');

        // Submit the correct flag
        $form = $crawler->selectButton('Valider')->form();
        $form['flag'] = 'DASH{score}';
        $client->submit($form);

        $this->assertResponseIsSuccessful();
        // Score should be 100 + 200 = 300
        $this->assertSelectorTextContains('.card-body', '300 pts');
    }
}
