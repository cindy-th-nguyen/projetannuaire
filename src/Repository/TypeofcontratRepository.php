<?php

namespace App\Repository;

use App\Entity\Typeofcontrat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Typeofcontrat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Typeofcontrat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Typeofcontrat[]    findAll()
 * @method Typeofcontrat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeofcontratRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Typeofcontrat::class);
    }

    // /**
    //  * @return Typeofcontrat[] Returns an array of Typeofcontrat objects
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
    public function findOneBySomeField($value): ?Typeofcontrat
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
