<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * Class UserToken
 * @package App\Entity
 * @ORM\Table(name="user_token")
 * @ORM\Entity
 */
class UserToken
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", length=128)
     */
    private $refreshPublicToken;

    /**
     * @var string
     * @ORM\Column(type="string", length=36, unique=true)
     */
    private $publicToken;

    /**
     * @var string
     * @ORM\Column(type="string", length=36, unique=true)
     */
    private $privateWebToken;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", length=128)
     */
    private $refreshWebToken;

    /**
     * @var string
     * @ORM\Column(type="string", length=36, unique=true)
     */
    private $privateMobileToken;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", length=128)
     */
    private $refreshMobileToken;

    /**
     * UserToken constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->publicToken = Uuid::uuid4();
        $this->privateMobileToken = Uuid::uuid4();
        $this->privateWebToken = Uuid::uuid4();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getRefreshPublicToken(): \DateTime
    {
        return $this->refreshPublicToken;
    }

    /**
     * @param \DateTime $refreshPublicToken
     */
    public function setRefreshPublicToken(\DateTime $refreshPublicToken): void
    {
        $this->refreshPublicToken = $refreshPublicToken;
    }

    /**
     * @return string
     */
    public function getPublicToken(): string
    {
        return $this->publicToken;
    }

    /**
     * @param string $publicToken
     */
    public function setPublicToken(string $publicToken): void
    {
        $this->publicToken = $publicToken;
    }

    /**
     * @return \DateTime
     */
    public function getRefreshWebToken(): \DateTime
    {
        return $this->refreshWebToken;
    }

    /**
     * @param \DateTime $refreshWebToken
     */
    public function setRefreshWebToken(\DateTime $refreshWebToken): void
    {
        $this->refreshWebToken = $refreshWebToken;
    }

    /**
     * @return string
     */
    public function getPrivateWebToken(): string
    {
        return $this->privateWebToken;
    }

    /**
     * @param string $privateWebToken
     */
    public function setPrivateWebToken(string $privateWebToken): void
    {
        $this->privateWebToken = $privateWebToken;
    }

    /**
     * @return \DateTime
     */
    public function getRefreshMobileToken(): \DateTime
    {
        return $this->refreshMobileToken;
    }

    /**
     * @param \DateTime $refreshMobileToken
     */
    public function setRefreshMobileToken(\DateTime $refreshMobileToken): void
    {
        $this->refreshMobileToken = $refreshMobileToken;
    }

    /**
     * @return string
     */
    public function getPrivateMobileToken(): string
    {
        return $this->privateMobileToken;
    }

    /**
     * @param string $privateMobileToken
     */
    public function setPrivateMobileToken(string $privateMobileToken): void
    {
        $this->privateMobileToken = $privateMobileToken;
    }
}
