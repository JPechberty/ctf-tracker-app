<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Admin;
use App\Entity\Challenge;
use App\Entity\Flag;
use App\Entity\Submission;
use App\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SubmissionCrudControllerTest extends WebTestCase
{
    private function createAdmin(): Admin
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $admin = new Admin();
        $admin->setUsername('submissionadmin' . uniqid());

        $hasher = $container->get(UserPasswordHasherInterface::class);
        $admin->setPassword($hasher->hashPassword($admin, 'password'));

        $entityManager->persist($admin);
        $entityManager->flush();

        return $admin;
    }

    private function createTestSubmission(): Submission
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $challenge = new Challenge();
        $challenge->setName('Submission Test Challenge ' . uniqid());
        $challenge->setPrefix('SUB');
        $challenge->setStartDate(new \DateTimeImmutable());
        $challenge->setEndDate(new \DateTimeImmutable('+1 day'));
        $entityManager->persist($challenge);

        $flag = new Flag();
        $flag->setName('Test Flag ' . uniqid());
        $flag->setValue('FLAG{test' . uniqid() . '}');
        $flag->setPoints(100);
        $flag->setChallenge($challenge);
        $entityManager->persist($flag);

        $team = new Team();
        $team->setName('Test Team ' . uniqid());
        $team->setUsername('subteam' . uniqid());
        $team->setPassword('password');
        $team->setChallenge($challenge);
        $entityManager->persist($team);

        $submission = new Submission();
        $submission->setTeam($team);
        $submission->setFlag($flag);
        $submission->setSubmittedValue('FLAG{test}');
        $submission->setSuccess(true);
        $entityManager->persist($submission);

        $entityManager->flush();

        return $submission;
    }

    public function testSubmissionListIsAccessible(): void
    {
        $client = static::createClient();
        $admin = $this->createAdmin();

        $client->loginUser($admin, 'admin');
        $client->request('GET', '/admin/submission');

        $this->assertResponseIsSuccessful();
    }

    public function testSubmissionListShowsSubmissions(): void
    {
        $client = static::createClient();
        $admin = $this->createAdmin();
        $submission = $this->createTestSubmission();

        $client->loginUser($admin, 'admin');
        $client->request('GET', '/admin/submission');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    public function testSubmissionDetailIsAccessible(): void
    {
        $client = static::createClient();
        $admin = $this->createAdmin();
        $submission = $this->createTestSubmission();

        $client->loginUser($admin, 'admin');
        $client->request('GET', '/admin/submission/' . $submission->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testSubmissionNewIsDisabled(): void
    {
        $client = static::createClient();
        $admin = $this->createAdmin();

        $client->loginUser($admin, 'admin');
        $client->request('GET', '/admin/submission/new');

        // Should show forbidden (action disabled)
        $this->assertResponseStatusCodeSame(403);
    }

    public function testSubmissionEditIsDisabled(): void
    {
        $client = static::createClient();
        $admin = $this->createAdmin();
        $submission = $this->createTestSubmission();

        $client->loginUser($admin, 'admin');
        $client->request('GET', '/admin/submission/' . $submission->getId() . '/edit');

        // Should show forbidden (action disabled)
        $this->assertResponseStatusCodeSame(403);
    }

    public function testSubmissionInMenu(): void
    {
        $client = static::createClient();
        $admin = $this->createAdmin();

        $client->loginUser($admin, 'admin');
        $crawler = $client->request('GET', '/admin/submission');

        $this->assertResponseIsSuccessful();
        // Check that Submissions link exists in the page menu/sidebar
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Submissions")')->count());
    }

    public function testSubmissionIsReadOnly(): void
    {
        $client = static::createClient();
        $admin = $this->createAdmin();

        $client->loginUser($admin, 'admin');
        $crawler = $client->request('GET', '/admin/submission');

        $this->assertResponseIsSuccessful();

        // Should not have "New" button
        $this->assertSelectorNotExists('a.action-new');
    }
}
