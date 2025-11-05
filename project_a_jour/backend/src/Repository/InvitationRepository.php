<?php

namespace App\Repository;

use App\Entity\Invitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invitation>
 *
 * @method Invitation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invitation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invitation[]    findAll()
 * @method Invitation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invitation::class);
    }

    public function findPendingInvitations(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.status = :status')
            ->setParameter('status', 'sent')
            ->andWhere('i.sentAt < :threshold')
            ->setParameter('threshold', new \DateTimeImmutable('-3 days'))
            ->getQuery()
            ->getResult();
    }

    public function getInvitationStatsByEvent(int $eventId): array
    {
        return $this->createQueryBuilder('i')
            ->select('i.status')
            ->addSelect('COUNT(i.id) as count')
            ->where('i.event = :eventId')
            ->setParameter('eventId', $eventId)
            ->groupBy('i.status')
            ->getQuery()
            ->getResult();
    }
}