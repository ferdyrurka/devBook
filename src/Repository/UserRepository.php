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
    public function getOneByPrivateWebToken(string $token): User
    {
        $user = $this->getEntityManager()->createQuery('
            SELECT p FROM App:User p JOIN p.userTokenReferences u WHERE u.privateWebToken = :token 
        ')
            ->setParameter(':token', $token)
            ->execute()
        ;

        if (empty($user)) {
            throw new UserNotFoundException('User by public token not found. Token have value ' . $token);
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
            SELECT p FROM App:User p WHERE p.id != :id AND p.firstName LIKE :phrase OR p.surname LIKE :phrase
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
            SELECT COUNT(con.messageId) FROM App:User p 
            INNER JOIN p.conversationReferences con 
            WHERE p.id = :userId OR p.id = :receiveId 
            GROUP BY con.messageId 
            HAVING COUNT(con.messageId) > 1
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
}
