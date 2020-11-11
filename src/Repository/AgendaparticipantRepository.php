<?php

namespace App\Repository;

use App\Entity\Agendaparticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Agendaparticipant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Agendaparticipant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Agendaparticipant[]    findAll()
 * @method Agendaparticipant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaparticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agendaparticipant::class);
    }

    // /**
    //  * @return Agendaparticipant[] Returns an array of Agendaparticipant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Agendaparticipant
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
