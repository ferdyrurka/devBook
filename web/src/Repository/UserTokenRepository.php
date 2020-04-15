<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class UserTokenRepository
 * @package App\Repository
 */
class UserTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserToken::class);
    }

    /**
     * @param UserToken $userToken
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(UserToken $userToken): void
    {
        $this->getEntityManager()->persist($userToken);
        $this->getEntityManager()->flush();
    }

    /**
     * @param UserToken $userToken
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(UserToken $userToken): void
    {
        $this->getEntityManager()->remove($userToken);
        $this->getEntityManager()->flush();
    }
}

