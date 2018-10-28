<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Notifications
 * @package App\Entity
 * @ORM\Table(name="notification")
 * @ORM\Entity()
 */
class Notification
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex(
     *     pattern="/^([A-ZĄĆĘŁŃÓŚŹŻ|a-ząćęłnóśźż|0-9| |,|.|-]){0,255}$/"
     * )
     */
    private $message;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", length=64)
     */
    private $date;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="User", inversedBy="notificationsReferences")
     * @ORM\JoinTable(name="notifications_user",
     *      joinColumns={
     *          @ORM\JoinColumn(name="notification_id", referencedColumnName="id")
     *      },
     *     inverseJoinColumns={
     *          @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *     }
     * )
     */
    private $userReferences;

    public function __construct()
    {
        $this->userReferences = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return self
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return self
     */
    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getUserReferences(): Collection
    {
        return $this->userReferences;
    }

    /**
     * @param Collection $userReferences
     * @return self
     */
    public function setUserReferences(Collection $userReferences): self
    {
        $this->userReferences = $userReferences;

        return $this;
    }
}
