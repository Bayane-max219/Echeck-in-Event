<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participant>
 *
 * @method Participant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participant[]    findAll()
 * @method Participant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    public function findByEventWithCheckInStatus(int $eventId): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.checkIns', 'c')
            ->where('p.event = :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('p.lastName', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCheckedInParticipants(int $eventId): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.checkIns', 'c')
            ->where('p.event = :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('c.checkedInAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getParticipantStatsByEvent(int $eventId): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.status')
            ->addSelect('COUNT(p.id) as count')
            ->where('p.event = :eventId')
            ->setParameter('eventId', $eventId)
            ->groupBy('p.status')
            ->getQuery()
            ->getResult();
    }
}