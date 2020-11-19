<?php

namespace App\Repository;

use App\Entity\PropertySale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PropertySale|null find($id, $lockMode = null, $lockVersion = null)
 * @method PropertySale|null findOneBy(array $criteria, array $orderBy = null)
 * @method PropertySale[]    findAll()
 * @method PropertySale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PropertySaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PropertySale::class);
    }

    // /**
    //  * @return PropertySale[] Returns an array of PropertySale objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PropertySale
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
