<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class NotificationRepository
 * @package App\Repository
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function findLastNotificationByUserId(int $userId): ?Notification
    {
        $notification = $this->getEntityManager()->createQuery('
            SELECT n FROM App:Notification n JOIN n.userReferences u WHERE u.id = :userId ORDER BY n.date DESC
        ')
            ->setMaxResults(1)
            ->setParameter(':userId', $userId)
            ->execute();

        if (empty($notification)) {
            return null;
        }

        return $notification[0];
    }
}

