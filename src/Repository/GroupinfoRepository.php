<?php

namespace App\Repository;

use App\Entity\Groupinfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Groupinfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Groupinfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Groupinfo[]    findAll()
 * @method Groupinfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupinfoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Groupinfo::class);
    }

    // /**
    //  * @return Groupinfo[] Returns an array of Groupinfo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Groupinfo
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
