<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Tutorial;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
            ->where('t.isPublished = true')
            ->orderBy('t.publishedAt', 'DESC')
            ->innerJoin('t.tags', 'c')
            ->andWhere('c.label = :tag')
            ->setParameter('tag', $label)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Tutorial[] Returns an array of Tutorial objects
     */
    public function findRelatedTutorials(Tutorial $tutorial, $limit = null)
    {
        $query = $this->createQueryBuilder('t')
            ->innerJoin('t.tags', 'c')
            ->addSelect('c')
            ->andWhere("c IN(:tags)")
            ->setParameter('tags', array_values($tutorial->getTags()->toArray()))
            ->andWhere('t.id != :id')
            ->setParameter('id', $tutorial->getId())
            ->andWhere('t.isPublished = true')
            ->orderBy('t.publishedAt', 'DESC');

        if ($limit) {
            $query->setMaxResults($limit);
        }

        return $query
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Tutorial Returns a Tutorial object or null
     */
    public function getUserLastPublishedTutorial(Tutorial $tutorial)
    {
        return $this->createQueryBuilder('t')
            ->where('t.author = :author')
            ->setParameter('author', $tutorial->getAuthor())
            ->andWhere('t.id != :id')
            ->setParameter('id', $tutorial->getId())
            ->orderBy('t.publishedAt', 'DESC')
            ->setMaxResults(1)
            ->andWhere('t.isPublished = true')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findVideoTutorials()
    {
        return $this->createQueryBuilder('t')
            ->where('t.videoLink is not null')
            ->andWhere('t.isPublished = true')
            ->orderBy('t.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTutorialsTotalPageViews()
    {
        return $this->createQueryBuilder('t')
            ->select('SUM(t.views) as views')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPublishedTutorials()
    {
        return $this->createQueryBuilder('t')
            ->where('t.isPublished = true')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getPublishedTutorialsQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->where('t.isPublished = true')
            ->orderBy('t.publishedAt', 'DESC');
    }

    public function getVideoTutorialsQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->where('t.videoLink is not null')
            ->andWhere('t.isPublished = true')
            ->orderBy('t.publishedAt', 'DESC');
    }

    public function getPublishedByTagQueryBuilder(string $label): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->where('t.isPublished = true')
            ->orderBy('t.publishedAt', 'DESC')
            ->innerJoin('t.tags', 'c')
            ->andWhere('c.label = :tag')
            ->setParameter('tag', $label);
    }

    public function getPublishedByUserQueryBuilder(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->where('t.isPublished = true')
            ->andWhere('t.author = :author')
            ->setParameter('author', $user)
            ->orderBy('t.publishedAt', 'DESC');
    }
}
