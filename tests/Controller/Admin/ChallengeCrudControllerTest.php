<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Admin;
use App\Entity\Challenge;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ChallengeCrudControllerTest extends WebTestCase
{
    private function createAuthenticatedAdmin($client): Admin
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $admin = new Admin();
        $admin->setUsername('crudtestadmin' . uniqid());

        $hasher = $container->get(UserPasswordHasherInterface::class);
        $admin->setPassword($hasher->hashPassword($admin, 'testpassword'));

        $entityManager->persist($admin);
        $entityManager->flush();

        return $admin;
    }

    public function testDashboardRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        $this->assertResponseRedirects('/admin/login');
    }

    public function testDashboardAccessibleAfterLogin(): void
    {
        $client = static::createClient();
        $admin = $this->createAuthenticatedAdmin($client);
        $client->loginUser($admin, 'admin');

        $client->request('GET', '/admin');

        // Dashboard redirects to challenge list
        $this->assertResponseRedirects('/admin/challenge');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testChallengeListIsAccessible(): void
    {
        $client = static::createClient();
        $admin = $this->createAuthenticatedAdmin($client);
        $client->loginUser($admin, 'admin');

        $client->request('GET', '/admin/challenge');

        $this->assertResponseIsSuccessful();
    }

    public function testChallengeNewFormIsAccessible(): void
    {
        $client = static::createClient();
        $admin = $this->createAuthenticatedAdmin($client);
        $client->loginUser($admin, 'admin');

        $client->request('GET', '/admin/challenge/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testCreateChallenge(): void
    {
        $client = static::createClient();
        $admin = $this->createAuthenticatedAdmin($client);
        $client->loginUser($admin, 'admin');

        $crawler = $client->request('GET', '/admin/challenge/new');

        $form = $crawler->filter('form[name="Challenge"]')->form();
        $form['Challenge[name]'] = 'Test Challenge ' . uniqid();
        $form['Challenge[description]'] = 'Test description';
        $form['Challenge[prefix]'] = 'TEST';
        $form['Challenge[startDate]'] = '2026-01-15 10:00:00';
        $form['Challenge[endDate]'] = '2026-01-15 18:00:00';

        $client->submit($form);

        $this->assertResponseRedirects();
    }

    public function testDateValidationRejectsInvalidDates(): void
    {
        $client = static::createClient();
        $admin = $this->createAuthenticatedAdmin($client);
        $client->loginUser($admin, 'admin');

        $crawler = $client->request('GET', '/admin/challenge/new');

        $form = $crawler->filter('form[name="Challenge"]')->form();
        $form['Challenge[name]'] = 'Invalid Date Challenge';
        $form['Challenge[prefix]'] = 'TEST';
        $form['Challenge[startDate]'] = '2026-01-15 18:00:00';
        $form['Challenge[endDate]'] = '2026-01-15 10:00:00';

        $client->submit($form);

        // Form should not redirect on validation error
        $this->assertResponseStatusCodeSame(422);
    }
}
