<?php

namespace App\Repository;

use App\Entity\Workon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Workon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Workon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Workon[]    findAll()
 * @method Workon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkonRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Workon::class);
    }

    // /**
    //  * @return Workon[] Returns an array of Workon objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Workon
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
