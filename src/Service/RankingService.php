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
}
