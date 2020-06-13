<?php

namespace App\Repository;

use App\Entity\Tutelle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Tutelle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tutelle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tutelle[]    findAll()
 * @method Tutelle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TutelleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tutelle::class);
    }

    // /**
    //  * @return Tutelle[] Returns an array of Tutelle objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Tutelle
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
