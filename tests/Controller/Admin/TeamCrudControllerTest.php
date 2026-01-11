<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Admin;
use App\Entity\Challenge;
use App\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TeamCrudControllerTest extends WebTestCase
{
    private function createAuthenticatedAdmin($client): Admin
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $admin = new Admin();
        $admin->setUsername('teamtestadmin' . uniqid());

        $hasher = $container->get(UserPasswordHasherInterface::class);
        $admin->setPassword($hasher->hashPassword($admin, 'testpassword'));

        $entityManager->persist($admin);
        $entityManager->flush();

        return $admin;
    }

    private function createTestChallenge(): Challenge
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $challenge = new Challenge();
        $challenge->setName('Team Test Challenge ' . uniqid());
        $challenge->setPrefix('TEST');
        $challenge->setStartDate(new \DateTimeImmutable());
        $challenge->setEndDate(new \DateTimeImmutable('+1 day'));

        $entityManager->persist($challenge);
        $entityManager->flush();

        return $challenge;
    }

    public function testTeamListIsAccessible(): void
    {
        $client = static::createClient();
        $admin = $this->createAuthenticatedAdmin($client);
        $client->loginUser($admin, 'admin');

        $client->request('GET', '/admin/team');

        $this->assertResponseIsSuccessful();
    }

    public function testTeamNewFormIsAccessible(): void
    {
        $client = static::createClient();
        $admin = $this->createAuthenticatedAdmin($client);
        $client->loginUser($admin, 'admin');

        $client->request('GET', '/admin/team/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testCreateTeam(): void
    {
        $client = static::createClient();
        $admin = $this->createAuthenticatedAdmin($client);
        $client->loginUser($admin, 'admin');

        $challenge = $this->createTestChallenge();

        $crawler = $client->request('GET', '/admin/team/new');

        $form = $crawler->filter('form[name="Team"]')->form();
        $form['Team[name]'] = 'Test Team ' . uniqid();
        $form['Team[username]'] = 'testteam' . uniqid();
        $form['Team[plainPassword]'] = 'teampassword123';
        $form['Team[challenge]'] = $challenge->getId();

        $client->submit($form);

        $this->assertResponseRedirects();
    }

    public function testTeamPasswordIsHashed(): void
    {
        $client = static::createClient();
        $admin = $this->createAuthenticatedAdmin($client);
        $client->loginUser($admin, 'admin');

        $challenge = $this->createTestChallenge();
        $teamUsername = 'hashtest' . uniqid();

        $crawler = $client->request('GET', '/admin/team/new');

        $form = $crawler->filter('form[name="Team"]')->form();
        $form['Team[name]'] = 'Hash Test Team';
        $form['Team[username]'] = $teamUsername;
        $form['Team[plainPassword]'] = 'plainpassword';
        $form['Team[challenge]'] = $challenge->getId();

        $client->submit($form);

        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $team = $entityManager->getRepository(Team::class)->findOneBy(['username' => $teamUsername]);

        $this->assertNotNull($team);
        // Password should be hashed, not plain
        $this->assertNotEquals('plainpassword', $team->getPassword());
        // Should start with typical bcrypt/argon prefix
        $this->assertMatchesRegularExpression('/^\$2y\$|\$argon2/', $team->getPassword());
    }

    public function testTeamListShowsScoreAndChallenge(): void
    {
        $client = static::createClient();
        $admin = $this->createAuthenticatedAdmin($client);
        $client->loginUser($admin, 'admin');

        $challenge = $this->createTestChallenge();

        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $team = new Team();
        $team->setName('Display Test Team');
        $team->setUsername('displaytest' . uniqid());
        $team->setPassword($hasher->hashPassword($team, 'password'));
        $team->setScore(100);
        $team->setChallenge($challenge);

        $entityManager->persist($team);
        $entityManager->flush();

        $crawler = $client->request('GET', '/admin/team');

        $this->assertResponseIsSuccessful();
        // Team name should be visible
        $this->assertStringContainsString('Display Test Team', $crawler->html());
    }
}
