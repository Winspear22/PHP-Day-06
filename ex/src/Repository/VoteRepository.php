<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Vote;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Vote>
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function computeReputation(User $user): int
    {
        $qb = $this->createQueryBuilder('v')
            ->select('COALESCE(SUM(CASE WHEN v.isLike = true THEN 1 ELSE -1 END), 0) as rep')
            ->join('v.post', 'p')
            ->where('p.author = :u')
            ->setParameter('u', $user);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }


//    /**
//     * @return Vote[] Returns an array of Vote objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Vote
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
