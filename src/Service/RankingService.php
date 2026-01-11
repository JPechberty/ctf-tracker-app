<?php

namespace App\Service;

use App\Entity\Team;
use App\Repository\TeamRepository;

class RankingService
{
    public function __construct(
        private TeamRepository $teamRepo
    ) {
    }

    public function getTeamRank(Team $team): int
    {
        $teams = $this->teamRepo->findByChallengeSortedByScore($team->getChallenge());

        $rank = 1;
        $previousScore = null;
        $sameRankCount = 0;

        foreach ($teams as $t) {
            if ($previousScore !== null && $t->getScore() < $previousScore) {
                $rank += $sameRankCount;
                $sameRankCount = 1;
            } else {
                $sameRankCount++;
            }

            if ($t->getId() === $team->getId()) {
                return $rank;
            }

            $previousScore = $t->getScore();
        }

        return $rank;
    }

    /**
     * @param Team[] $teams
     * @return array<array{rank: int, team: Team}>
     */
    public function getRankedTeams(array $teams): array
    {
        $result = [];
        $rank = 1;
        $previousScore = null;
        $sameRankCount = 0;

        foreach ($teams as $team) {
            if ($previousScore !== null && $team->getScore() < $previousScore) {
                $rank += $sameRankCount;
                $sameRankCount = 1;
            } else {
                $sameRankCount++;
            }

            $result[] = [
                'rank' => $rank,
                'team' => $team,
            ];

            $previousScore = $team->getScore();
        }

        return $result;
    }
}
