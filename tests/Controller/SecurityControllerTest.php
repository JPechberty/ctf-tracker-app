<?php

namespace App\Tests\Controller;

use App\Entity\Admin;
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
        $admin->setUsername('testadmin');

        $hasher = $container->get(UserPasswordHasherInterface::class);
        $admin->setPassword($hasher->hashPassword($admin, 'testpassword'));

        $entityManager->persist($admin);
        $entityManager->flush();

        $crawler = $client->request('GET', '/admin/login');

        $form = $crawler->selectButton('Connexion')->form([
            '_username' => 'testadmin',
            '_password' => 'testpassword',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/admin');
        $client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Administration CTF Tracker');
    }

    public function testLogout(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $admin = new Admin();
        $admin->setUsername('logoutadmin');

        $hasher = $container->get(UserPasswordHasherInterface::class);
        $admin->setPassword($hasher->hashPassword($admin, 'testpassword'));

        $entityManager->persist($admin);
        $entityManager->flush();

        $client->loginUser($admin);

        $client->request('GET', '/admin/logout');
        $this->assertResponseRedirects();

        $client->followRedirect();
        $client->request('GET', '/admin');
        $this->assertResponseRedirects('/admin/login');
    }
}
