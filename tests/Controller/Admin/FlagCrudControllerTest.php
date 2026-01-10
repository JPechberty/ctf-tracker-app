<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Admin;
use App\Entity\Challenge;
use App\Entity\Flag;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FlagCrudControllerTest extends WebTestCase
{
    private function createAuthenticatedAdmin($client): Admin
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $admin = new Admin();
        $admin->setUsername('flagtestadmin' . uniqid());

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
        $challenge->setName('Test Challenge ' . uniqid());
        $challenge->setPrefix('TEST');
        $challenge->setStartDate(new \DateTimeImmutable());
        $challenge->setEndDate(new \DateTimeImmutable('+1 day'));

        $entityManager->persist($challenge);
        $entityManager->flush();

        return $challenge;
    }

    public function testFlagListIsAccessible(): void
    {
        $client = static::createClient();
        $admin = $this->createAuthenticatedAdmin($client);
        $client->loginUser($admin, 'admin');

        $client->request('GET', '/admin/flag');

        $this->assertResponseIsSuccessful();
    }

    public function testFlagNewFormIsAccessible(): void
    {
        $client = static::createClient();
        $admin = $this->createAuthenticatedAdmin($client);
        $client->loginUser($admin, 'admin');

        $client->request('GET', '/admin/flag/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testCreateFlag(): void
    {
        $client = static::createClient();
        $admin = $this->createAuthenticatedAdmin($client);
        $client->loginUser($admin, 'admin');

        $challenge = $this->createTestChallenge();

        $crawler = $client->request('GET', '/admin/flag/new');

        $form = $crawler->filter('form[name="Flag"]')->form();
        $form['Flag[name]'] = 'Test Flag ' . uniqid();
        $form['Flag[value]'] = 'FLAG{test123}';
        $form['Flag[points]'] = 100;
        $form['Flag[challenge]'] = $challenge->getId();

        $client->submit($form);

        $this->assertResponseRedirects();
    }

    public function testFlagValueHiddenInList(): void
    {
        $client = static::createClient();
        $admin = $this->createAuthenticatedAdmin($client);
        $client->loginUser($admin, 'admin');

        $challenge = $this->createTestChallenge();

        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $flag = new Flag();
        $flag->setName('Hidden Value Flag');
        $flag->setValue('FLAG{secret}');
        $flag->setPoints(50);
        $flag->setChallenge($challenge);

        $entityManager->persist($flag);
        $entityManager->flush();

        $crawler = $client->request('GET', '/admin/flag');

        $this->assertResponseIsSuccessful();
        // Value should not appear in the list
        $this->assertStringNotContainsString('FLAG{secret}', $crawler->html());
    }

    public function testCascadeDeleteRemovesFlags(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $challenge = new Challenge();
        $challenge->setName('Cascade Test Challenge ' . uniqid());
        $challenge->setPrefix('CASCADE');
        $challenge->setStartDate(new \DateTimeImmutable());
        $challenge->setEndDate(new \DateTimeImmutable('+1 day'));

        $flag = new Flag();
        $flag->setName('Cascade Test Flag');
        $flag->setValue('FLAG{cascade}');
        $flag->setPoints(25);

        $challenge->addFlag($flag);

        $entityManager->persist($challenge);
        $entityManager->flush();

        $flagId = $flag->getId();
        $challengeId = $challenge->getId();

        // Remove the challenge
        $entityManager->remove($challenge);
        $entityManager->flush();

        // Flag should be deleted too
        $deletedFlag = $entityManager->getRepository(Flag::class)->find($flagId);
        $this->assertNull($deletedFlag);
    }
}
