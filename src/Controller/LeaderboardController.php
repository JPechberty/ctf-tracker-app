<?php

namespace App\Controller;

use App\Repository\ChallengeRepository;
use App\Repository\TeamRepository;
use App\Service\RankingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LeaderboardController extends AbstractController
{
    #[Route('/leaderboard', name: 'app_leaderboard')]
    public function index(
        ChallengeRepository $challengeRepo,
        TeamRepository $teamRepo,
        RankingService $rankingService,
    ): Response {
        // For MVP, assume single active challenge
        $challenge = $challengeRepo->findOneBy([], ['id' => 'DESC']);

        if (!$challenge) {
            throw $this->createNotFoundException('No challenge found');
        }

        $teams = $teamRepo->findByChallengeSortedByScore($challenge);
        $rankedTeams = $rankingService->getRankedTeams($teams);

        return $this->render('leaderboard/index.html.twig', [
            'challenge' => $challenge,
            'rankedTeams' => $rankedTeams,
            'totalFlags' => count($challenge->getFlags()),
        ]);
    }
}
