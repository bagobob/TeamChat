<?php

namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conversation[]    findAll()
 * @method Conversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    // /**
    //  * @return Conversation[] Returns an array of Conversation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    'Groupe','gr',Join::WITH, $qb->expr()->eq('gr.user',':user')
    */
    public function findConversationsByUser(int $userId)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->
            select('otherUser.username','c.id as conversationId', 'lm.content', 'lm.createdAt','gr.nom')
            ->innerJoin('c.participants','p',Join::WITH, $qb->expr()->neq('p.user',':user'))
            ->innerJoin('c.participants','me',Join::WITH, $qb->expr()->eq('me.user',':user'))
            ->leftJoin('c.lastMessage','lm')
            ->leftJoin('c.groupes','gr',Join::WITH, $qb->expr()->eq('gr.conversation','c.id'))
            ->innerJoin('me.user','meUser')
            ->innerJoin('p.user','otherUser')
            ->where('meUser.id = :user')
            ->setParameter('user', $userId)
            ->orderBy('lm.createdAt','DESC')
            ->groupBy('c.id')
        ;
        //dd($qb->getQuery()->getResult());
        return $qb->getQuery()->getResult();
    }

    private function findConversationByParticipants(int $otherUserId, int $myId)
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->select($qb->expr()->count('p.conversation'))
            ->innerJoin('c.participants','p')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->eq('p.user',':me'),
                    $qb->expr()->eq('p.user',':otherUser')
                )
            )
            ->groupBy('p.conversation')
            ->having(
                $qb->expr()->eq(
                    $qb->expr()->count('p.conversation'),
                    2
                )
            )
            ->setParametrs([
                'me' => $myId,
                'otherUser' => $otherUserId
            ])
        ;

        return $qb->getQuery()->getResult();
    }
}
