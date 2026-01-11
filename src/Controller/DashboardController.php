<?php

namespace App\Controller;

use App\Entity\Submission;
use App\Entity\Team;
use App\Repository\SubmissionRepository;
use App\Service\FlagValidationService;
use App\Service\RankingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_TEAM')]
    public function index(
        Request $request,
        FlagValidationService $flagValidator,
        RankingService $rankingService,
        SubmissionRepository $submissionRepository,
        EntityManagerInterface $em,
    ): Response {
        /** @var Team $team */
        $team = $this->getUser();
        $challenge = $team->getChallenge();

        $feedback = null;
        $lastValue = '';

        if ($request->isMethod('POST')) {
            $submittedValue = trim($request->request->get('flag', ''));
            $lastValue = $submittedValue;

            $result = $flagValidator->validateSubmission($team, $submittedValue);

            // Persist submission (success or failure)
            $submission = new Submission();
            $submission->setTeam($team);
            $submission->setSubmittedValue($submittedValue);
            $submission->setSuccess($result->success);

            if ($result->flag) {
                $submission->setFlag($result->flag);
            }

            if ($result->success) {
                $team->addPoints($result->points);
                $lastValue = ''; // Clear on success
            }

            $em->persist($submission);
            $em->flush();

            $feedback = $result;
        }

        return $this->render('dashboard/index.html.twig', [
            'team' => $team,
            'challenge' => $challenge,
            'rank' => $rankingService->getTeamRank($team),
            'validatedFlags' => $submissionRepository->findValidatedByTeam($team),
            'totalFlags' => count($challenge->getFlags()),
            'feedback' => $feedback,
            'lastValue' => $lastValue,
        ]);
    }

    #[Route('/leaderboard', name: 'app_leaderboard')]
    public function leaderboard(): Response
    {
        // Placeholder - will be implemented in Story 3.4
        return $this->render('dashboard/leaderboard.html.twig');
    }
}
