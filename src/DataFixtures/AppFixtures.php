<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Challenge;
use App\Entity\Flag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Development fixtures for CTF Tracker application.
 *
 * Load fixtures with: php bin/console doctrine:fixtures:load --no-interaction
 * Purge and reload: php bin/console doctrine:fixtures:load --purge-with-truncate --no-interaction
 */
class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Admin account
        $admin = new Admin();
        $admin->setUsername('admin');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Challenge
        $challenge = new Challenge();
        $challenge->setName('Hackathon Red Team Cyber 2026');
        $challenge->setDescription('Challenge de cybersécurité pour les étudiants');
        $challenge->setPrefix('FLAG');
        $challenge->setStartDate(new \DateTimeImmutable('2026-02-01 09:00:00'));
        $challenge->setEndDate(new \DateTimeImmutable('2026-02-01 18:00:00'));
        $manager->persist($challenge);

        // Flags with varying points
        $flags = [
            ['name' => 'Web Exploitation', 'value' => 'FLAG{w3b_m4st3r}', 'points' => 100],
            ['name' => 'Crypto Challenge', 'value' => 'FLAG{cr4ck3d_1t}', 'points' => 250],
            ['name' => 'Reverse Engineering', 'value' => 'FLAG{r3v3rs3d}', 'points' => 500],
        ];

        foreach ($flags as $flagData) {
            $flag = new Flag();
            $flag->setName($flagData['name']);
            $flag->setValue($flagData['value']);
            $flag->setPoints($flagData['points']);
            $flag->setChallenge($challenge);
            $manager->persist($flag);
        }

        $manager->flush();
    }
}
