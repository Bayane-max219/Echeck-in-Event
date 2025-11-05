<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
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

    public function findUpcomingEvents(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.organizer = :user')
            ->andWhere('e.startDate > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime())
            ->orderBy('e.startDate', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findActiveEvents(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.organizer = :user')
            ->andWhere('e.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'active')
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getEventStatistics(User $user): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id) as total_events')
            ->addSelect('SUM(CASE WHEN e.status = :active THEN 1 ELSE 0 END) as active_events')
            ->addSelect('SUM(CASE WHEN e.startDate > :now THEN 1 ELSE 0 END) as upcoming_events')
            ->where('e.organizer = :user')
            ->setParameter('user', $user)
            ->setParameter('active', 'active')
            ->setParameter('now', new \DateTime());

        return $qb->getQuery()->getSingleResult();
    }
}