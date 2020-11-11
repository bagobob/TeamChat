<?php

namespace App\Repository;

use App\Entity\Agendauser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Agendauser|null find($id, $lockMode = null, $lockVersion = null)
 * @method Agendauser|null findOneBy(array $criteria, array $orderBy = null)
 * @method Agendauser[]    findAll()
 * @method Agendauser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendauserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agendauser::class);
    }

    // /**
    //  * @return Agendauser[] Returns an array of Agendauser objects
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
    public function findOneBySomeField($value): ?Agendauser
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findAgendaByUser(int $userId)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->
            select('otherUser.username','c.id as agendaId')
            ->innerJoin('c.','me',Join::WITH, $qb->expr()->eq('me.user',':user'))
            ->leftJoin('c.user','user')
            ->innerJoin('me.user','meUser')
            ->where('meUser.id = :user')
            ->setParameter('user', $userId)
        ;
        //dd($qb->getQuery());
        return $qb->getQuery()->getResult();
}
}