<?php

namespace App\Tests\DataFixtures;

use App\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AppFixturesTest extends KernelTestCase
{
    public function testFixturesCreateThreeTeams(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $teamRepository = $entityManager->getRepository(Team::class);
        $teams = $teamRepository->findAll();

        // Should have at least 3 teams from fixtures (may have more from other tests)
        $this->assertGreaterThanOrEqual(3, count($teams));
    }

    public function testTeamFixturesHaveCorrectData(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $teamRepository = $entityManager->getRepository(Team::class);

        // Check team1 exists
        $team1 = $teamRepository->findOneBy(['username' => 'team1']);
        $this->assertNotNull($team1);
        $this->assertEquals('Les Hackers', $team1->getName());
        $this->assertEquals(0, $team1->getScore());
        $this->assertNotNull($team1->getChallenge());

        // Check team2 exists
        $team2 = $teamRepository->findOneBy(['username' => 'team2']);
        $this->assertNotNull($team2);
        $this->assertEquals('Cyber Squad', $team2->getName());

        // Check team3 exists
        $team3 = $teamRepository->findOneBy(['username' => 'team3']);
        $this->assertNotNull($team3);
        $this->assertEquals('Binary Breakers', $team3->getName());
    }

    public function testTeamsAssociatedWithChallenge(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $teamRepository = $entityManager->getRepository(Team::class);

        $team1 = $teamRepository->findOneBy(['username' => 'team1']);
        $team2 = $teamRepository->findOneBy(['username' => 'team2']);
        $team3 = $teamRepository->findOneBy(['username' => 'team3']);

        // All teams should have the same challenge
        $this->assertNotNull($team1->getChallenge());
        $this->assertNotNull($team2->getChallenge());
        $this->assertNotNull($team3->getChallenge());

        $this->assertEquals($team1->getChallenge()->getId(), $team2->getChallenge()->getId());
        $this->assertEquals($team2->getChallenge()->getId(), $team3->getChallenge()->getId());
    }

    public function testTeamPasswordsAreHashed(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $teamRepository = $entityManager->getRepository(Team::class);
        $team1 = $teamRepository->findOneBy(['username' => 'team1']);

        // Password should be hashed (not plain text)
        $this->assertNotEquals('team1pass', $team1->getPassword());
        $this->assertStringStartsWith('$', $team1->getPassword()); // Hashed passwords start with $
    }
}
