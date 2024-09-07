<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

   
public function findByName(string $name, string $sortOrder = 'asc')
{
    return $this->createQueryBuilder('e')
        ->where('e.nom LIKE :name')
        ->setParameter('name', '%' . $name . '%')
        ->orderBy('e.nom', $sortOrder)
        ->getQuery()
        ->getResult();
}
    
public function getStatsByLocation(): array
{
    $qb = $this->createQueryBuilder('e')
        ->select('e.emplacement, COUNT(e.id) AS number_of_places')
        ->groupBy('e.emplacement')
        ->orderBy('number_of_places', 'DESC');
    
    return $qb->getQuery()->getResult();
}

public function findAllOrderedByName(): array
{
    return $this->createQueryBuilder('e')
        ->orderBy('e.nom', 'ASC')
        ->getQuery()
        ->getResult();
}



//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
