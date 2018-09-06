<?php
declare(strict_types=1);


namespace App\Repository;

use App\Entity\User;
use App\Exception\UserNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class UserRepository
 * @package App\Repository
 */
class UserRepository extends ServiceEntityRepository
{
    /**
     * UserRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param int $userId
     * @return User
     * @throws UserNotFoundException
     */
    public function getOneById(int $userId): User
    {
        $user = parent::find($userId);

        if (!$user) {
            throw new UserNotFoundException('Does user not found');
        }

        return $user;
    }
}
