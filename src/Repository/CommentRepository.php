<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Tutorial;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getTutorialComments(Tutorial $tutorial)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.tutorial = :tutorial')
            ->setParameter('tutorial', $tutorial)
            ->andWhere('c.state = :submitted OR c.state = :published')
            ->setParameter('submitted', Comment::STATE_SUBMITTED)
            ->setParameter('published', Comment::STATE_PUBLISHED)
            ->andWhere('c.replyTo is NULL')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function findOlderSpams(int $days)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.state = :state')
            ->setParameter('state', Comment::STATE_SPAM)
            ->andWhere("c.createdAt < :date")
            ->setParameter('date', new \DateTime("-{$days} days"))
            ->getQuery()
            ->getResult();
    }

    public function countPublishedComments()
    {
        return $this->createQueryBuilder('c')
            ->where('c.state = :state')
            ->setParameter('state', Comment::STATE_PUBLISHED)
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
