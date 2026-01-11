<?php

namespace App\Repository;

use App\Entity\Submission;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Submission>
 */
class SubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Submission::class);
    }

    /**
     * @return Submission[]
     */
    public function findValidatedByTeam(Team $team): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.flag', 'f')
            ->andWhere('s.team = :team')
            ->andWhere('s.success = true')
            ->setParameter('team', $team)
            ->orderBy('s.submittedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
