<?php

namespace App\Repository;

use App\Entity\Tutorial;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Tutorial|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tutorial|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tutorial[]    findAll()
 * @method Tutorial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TutorialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tutorial::class);
    }

    /**
     * @return Tutorial[] Returns an array of Tutorial objects
     */
    public function findAllPublishedByTag(string $label)
    {
        return $this->createQueryBuilder('t')
            ->where('t.isPublished = :isPublished')
            ->setParameter('isPublished', true)
            ->orderBy('t.publishedAt', 'DESC')
            ->innerJoin('t.tags', 'c')
            ->andWhere('c.label = :tag')
            ->setParameter('tag', $label)
            ->getQuery()
            ->getResult();
    }


    /*
    public function findOneBySomeField($value): ?Tutorial
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
