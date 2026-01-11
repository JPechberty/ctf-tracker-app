<?php

namespace App\Controller;

use App\Entity\Team;
use App\Repository\SubmissionRepository;
use App\Service\RankingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_TEAM')]
    public function index(RankingService $rankingService, SubmissionRepository $submissionRepository): Response
    {
        /** @var Team $team */
        $team = $this->getUser();

        return $this->render('dashboard/index.html.twig', [
            'team' => $team,
            'challenge' => $team->getChallenge(),
            'rank' => $rankingService->getTeamRank($team),
            'validatedFlags' => $submissionRepository->findValidatedByTeam($team),
            'totalFlags' => count($team->getChallenge()->getFlags()),
        ]);
    }

    #[Route('/leaderboard', name: 'app_leaderboard')]
    public function leaderboard(): Response
    {
        // Placeholder - will be implemented in Story 3.4
        return $this->render('dashboard/leaderboard.html.twig');
    }
}
