<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * Class MessageUser
 * @package App\Entity
 * @ORM\Table(name="conversation")
 * @ORM\Entity
 */
class Conversation
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=36)
     */
    private $messageId;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="User", inversedBy="conversationReferences")
     * @ORM\JoinTable(name="conversation_user",
     *      joinColumns={
     *          @ORM\JoinColumn(name="conversation_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *      },
     * )
     */
    private $userReferences;

    /**
     * MessageUser constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->messageId = Uuid::uuid4();
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
    public function getMessageId(): string
    {
        return $this->messageId;
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
     */
    public function setUserReferences(Collection $userReferences): void
    {
        $this->userReferences = $userReferences;
    }

    /**
     * @param User $user
     * @return Conversation
     */
    public function addConversation(User $user): self
    {
        $this->getUserReferences()->add($user);
        return $this;
    }
}
