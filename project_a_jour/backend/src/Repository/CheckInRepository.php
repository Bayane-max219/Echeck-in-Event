<?php

namespace App\Repository;

use App\Entity\CheckIn;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CheckIn>
 *
 * @method CheckIn|null find($id, $lockMode = null, $lockVersion = null)
 * @method CheckIn|null findOneBy(array $criteria, array $orderBy = null)
 * @method CheckIn[]    findAll()
 * @method CheckIn[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheckInRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CheckIn::class);
    }

    public function findByEventWithParticipant(int $eventId): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.participant', 'p')
            ->innerJoin('c.event', 'e')
            ->where('e.id = :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('c.checkedInAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getCheckInStatsByEvent(int $eventId): array
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id) as total_checkins')
            ->addSelect('DATE(c.checkedInAt) as checkin_date')
            ->where('c.event = :eventId')
            ->setParameter('eventId', $eventId)
            ->groupBy('checkin_date')
            ->orderBy('checkin_date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne le dernier check-in pour chaque événement (groupé par event).
     * @return CheckIn[]
     */
    public function findLatestCheckInPerEvent(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT c.* FROM check_in c
                INNER JOIN (
                    SELECT event_id, MAX(checked_in_at) as max_checked_in_at
                    FROM check_in
                    GROUP BY event_id
                ) latest ON c.event_id = latest.event_id AND c.checked_in_at = latest.max_checked_in_at
                ORDER BY latest.max_checked_in_at DESC';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        $checkInsData = $result->fetchAllAssociative();
        // Hydrate CheckIn entities from raw data
        $checkIns = [];
        foreach ($checkInsData as $data) {
            $checkIn = $this->find($data['id']);
            if ($checkIn) {
                $checkIns[] = $checkIn;
            }
        }
        return $checkIns;
    }
}