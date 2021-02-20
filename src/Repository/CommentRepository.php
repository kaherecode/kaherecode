<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Tutorial;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
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


    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
