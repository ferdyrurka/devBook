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

    /**
     * @param string $token
     * @throws UserNotFoundException
     * @return User
     */
    public function getOneByPublicToken(string $token): User
    {
        $user = $this->getEntityManager()->createQuery('
            SELECT p FROM App:User p JOIN p.userTokenReferences u WHERE u.publicToken = :token 
        ')
            ->setMaxResults(1)
            ->setParameter(':token', $token)
            ->execute()
        ;

        if (empty($user)) {
            throw new UserNotFoundException('User by public token not found. Token have value ' . $token);
        }

        return $user[0];
    }

    /**
     * @param string $token
     * @throws UserNotFoundException
     * @return User
     */
    public function getOneByPrivateWebTokenOrMobileToken(string $token): User
    {
        $user = $this->getEntityManager()->createQuery('
            SELECT p FROM App:User p JOIN p.userTokenReferences u WHERE u.privateWebToken = :token  OR u.privateMobileToken = :token
        ')
            ->setMaxResults(1)
            ->setParameter(':token', $token)
            ->execute()
        ;

        if (empty($user)) {
            throw new UserNotFoundException('User by private token not found. Token have value ' . $token);
        }

        return $user[0];
    }

    /**
     * Not search this user
     *
     * @param string $phrase
     * @param integer $userId
     * @return array|null
     */
    public function findByFirstNameOrSurname(string $phrase, int $userId): ?array
    {
        return $this->getEntityManager()->createQuery('
            SELECT p FROM App:User p WHERE p.id != :id AND ( p.firstName LIKE :phrase OR p.surname LIKE :phrase )  
        ')
            ->setParameter(':phrase', '%' . $phrase . '%')
            ->setParameter(':id', $userId)
            ->execute()
            ;
    }

    /**
     * @param int $userId
     * @param int $receiveId
     * @return int
     */
    public function getCountConversationByUsersId(int $userId, int $receiveId): int
    {
        $conversationsCount = $this->getEntityManager()->createQuery('
            SELECT COUNT(con.conversationId) FROM App:User p 
            INNER JOIN p.conversationReferences con 
            WHERE p.id = :userId OR p.id = :receiveId 
            GROUP BY con.conversationId 
            HAVING COUNT(con.conversationId) > 1
        ')
            ->setParameter(':userId', $userId)
            ->setParameter(':receiveId', $receiveId)
            ->execute()
        ;

        if (isset($conversationsCount[0][1])) {
            return (int) $conversationsCount[0][1];
        }

        return 0;
    }

    /**
     * @param User $user
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @param User $user
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(User $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }
}
