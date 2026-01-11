<?php

namespace App\Tests\Controller;

use App\Entity\Admin;
use App\Entity\Challenge;
use App\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    public function testProtectedRouteRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        $this->assertResponseRedirects('/admin/login');
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/login');

        $form = $crawler->selectButton('Connexion')->form([
            '_username' => 'invalid',
            '_password' => 'invalid',
        ]);
        $client->submit($form);

        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-error', 'Identifiants incorrects');
    }

    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $admin = new Admin();
        $admin->setUsername('testadmin' . uniqid());

        $hasher = $container->get(UserPasswordHasherInterface::class);
        $admin->setPassword($hasher->hashPassword($admin, 'testpassword'));

        $entityManager->persist($admin);
        $entityManager->flush();

        $crawler = $client->request('GET', '/admin/login');

        $form = $crawler->selectButton('Connexion')->form([
            '_username' => $admin->getUsername(),
            '_password' => 'testpassword',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects();
    }

    public function testLogout(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $admin = new Admin();
        $admin->setUsername('logoutadmin' . uniqid());

        $hasher = $container->get(UserPasswordHasherInterface::class);
        $admin->setPassword($hasher->hashPassword($admin, 'testpassword'));

        $entityManager->persist($admin);
        $entityManager->flush();

        $client->loginUser($admin, 'admin');

        $client->request('GET', '/admin/logout');
        $this->assertResponseRedirects();

        $client->followRedirect();
        $client->request('GET', '/admin');
        $this->assertResponseRedirects('/admin/login');
    }

    // Team Authentication Tests

    private function createTestChallenge(): Challenge
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $challenge = new Challenge();
        $challenge->setName('Auth Test Challenge ' . uniqid());
        $challenge->setPrefix('AUTH');
        $challenge->setStartDate(new \DateTimeImmutable());
        $challenge->setEndDate(new \DateTimeImmutable('+1 day'));

        $entityManager->persist($challenge);
        $entityManager->flush();

        return $challenge;
    }

    public function testTeamLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    public function testDashboardRedirectsToLoginWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard');

        $this->assertResponseRedirects('/login');
    }

    public function testTeamLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'invalidteam',
            '_password' => 'invalidpassword',
        ]);
        $client->submit($form);

        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', 'Identifiants incorrects');
    }

    public function testTeamLoginWithValidCredentials(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $challenge = $this->createTestChallenge();

        $team = new Team();
        $team->setName('Login Test Team');
        $team->setUsername('loginteam' . uniqid());
        $team->setChallenge($challenge);

        $hasher = $container->get(UserPasswordHasherInterface::class);
        $team->setPassword($hasher->hashPassword($team, 'teampassword'));

        $entityManager->persist($team);
        $entityManager->flush();

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => $team->getUsername(),
            '_password' => 'teampassword',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/dashboard');
    }

    public function testTeamCanAccessDashboardAfterLogin(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $challenge = $this->createTestChallenge();

        $team = new Team();
        $team->setName('Dashboard Test Team');
        $team->setUsername('dashboardteam' . uniqid());
        $team->setChallenge($challenge);

        $hasher = $container->get(UserPasswordHasherInterface::class);
        $team->setPassword($hasher->hashPassword($team, 'teampassword'));

        $entityManager->persist($team);
        $entityManager->flush();

        $client->loginUser($team, 'main');

        $client->request('GET', '/dashboard');
        $this->assertResponseIsSuccessful();
    }

    public function testTeamLogout(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $challenge = $this->createTestChallenge();

        $team = new Team();
        $team->setName('Logout Test Team');
        $team->setUsername('logoutteam' . uniqid());
        $team->setChallenge($challenge);

        $hasher = $container->get(UserPasswordHasherInterface::class);
        $team->setPassword($hasher->hashPassword($team, 'teampassword'));

        $entityManager->persist($team);
        $entityManager->flush();

        $client->loginUser($team, 'main');

        $client->request('GET', '/logout');
        $this->assertResponseRedirects();

        $client->followRedirect();
        $client->request('GET', '/dashboard');
        $this->assertResponseRedirects('/login');
    }

    public function testAdminAndTeamFirewallsAreSeparate(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $challenge = $this->createTestChallenge();

        // Create team
        $team = new Team();
        $team->setName('Firewall Test Team');
        $team->setUsername('firewallteam' . uniqid());
        $team->setChallenge($challenge);

        $hasher = $container->get(UserPasswordHasherInterface::class);
        $team->setPassword($hasher->hashPassword($team, 'teampassword'));

        $entityManager->persist($team);
        $entityManager->flush();

        // Login as team on main firewall
        $client->loginUser($team, 'main');

        // Team should not access admin area
        $client->request('GET', '/admin');
        $this->assertResponseRedirects('/admin/login');
    }
}
