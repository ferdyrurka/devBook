<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User
 * @package App\Entity
 * @ORM\Table(name="user")
 * @ORM\Entity
 * @UniqueEntity("username")
 */
class User implements UserInterface, \Serializable
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", length=11)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=24)
     * @Assert\NotBlank(message="not.blank.fields")
     * @Assert\Length(
     *      min=3,
     *      max=24,
     *      minMessage="min.length {{limit}}",
     *      maxMessage="max.length {{limit}}",
     * )
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=32)
     * @Assert\NotBlank(message="not.blank.fields")
     * @Assert\Length(
     *      min=4,
     *      max=32,
     *      minMessage="min.length {{limit}}",
     *      maxMessage="max.length {{limit}}",
     * )
     */
    private $surname;

    /**
     * @Assert\NotBlank(message="not.blank.fields")
     * @Assert\Email(
     *     message = "incorrect data provided",
     * )
     * @ORM\Column(name="email",type="string", length=34, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @Assert\NotBlank(message="not.blank.fields")
     * @Assert\Length(
     *      min=8,
     *      max=24,
     *      minMessage="min.length {{limit}}",
     *      maxMessage="max.length {{limit}}",
     * )
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $roles;

    /**
     * @var string|null
     *
     * @ORM\Column(type="integer", length=2)
     * @Assert\NotBlank()
     */
    private $sex;

    /**
     * @var string|null
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="datetime", length=24)
     */
    private $dateBirth;

    /**
     * @var string
     *
     * @ORM\Column(type="datetime", length=24)
     */
    private $createdAt;

    /**
     * @var string
     * @ORM\Column(type="integer", length=11, name="token_id")
     */
    private $tokenId;

    /**
     * @var UserToken
     * @ORM\ManyToOne(targetEntity="UserToken")
     * @ORM\JoinColumn(name="token_id", referencedColumnName="id")
     */
    private $userTokenReferences;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="Conversation", mappedBy="userReferences")
     */
    private $conversationReferences;

    /**
     * @return int
     */
    public function getId() :int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     */
    public function setSurname(string $surname)
    {
        $this->surname = $surname;
    }

    /**
     * @return null|string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getSalt() :string
    {
        return $this->getUsername();
    }

    /**
     * @return string
     */
    public function getPassword() :string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @return null|string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword(string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @param string $roles
     */
    public function setRoles(string $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @return array
     */
    public function getRoles() :array
    {
        return [$this->roles];
    }

    /**
     * @return int
     */
    public function getStatus() :int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateBirth(): ?\DateTime
    {
        return $this->dateBirth;
    }

    /**
     * @param \DateTime $dateBirth
     */
    public function setDateBirth(\DateTime $dateBirth): void
    {
        $this->dateBirth = $dateBirth;
    }

    /**
     * @return int|null
     */
    public function getSex(): ?int
    {
        return $this->sex;
    }

    /**
     * @param int $sex
     */
    public function setSex(int $sex): void
    {
        $this->sex = $sex;
    }

    /**
     * @return string
     */
    public function getTokenId(): string
    {
        return $this->tokenId;
    }

    /**
     * @return UserToken
     */
    public function getUserTokenReferences(): UserToken
    {
        return $this->userTokenReferences;
    }

    /**
     * @param UserToken $userTokenReferences
     */
    public function setUserTokenReferences(UserToken $userTokenReferences): void
    {
        $this->userTokenReferences = $userTokenReferences;
    }

    /**
     * @return Collection
     */
    public function getConversationReferences(): Collection
    {
        return $this->conversationReferences;
    }

    /**
     *
     */
    public function eraseCredentials()
    {
        //
    }

    /**
     * @return int
     */
    public function isAccountNonExpired() :int
    {
        return 1;
    }

    /**
     * @return int
     */
    public function isAccountNonLocked() :int
    {
        return 1;
    }

    /**
     * @return int
     */
    public function isCredentialsNonExpired() :int
    {
        return 1;
    }

    /**
     * @return int
     */
    public function isEnabled() :int
    {
        return $this->status;
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo(UserInterface $user) :bool
    {
        if ($this->password !== $user->getPassword()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->firstName,
            $this->surname,
            $this->username,
            $this->password,
            $this->status,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->firstName,
            $this->surname,
            $this->username,
            $this->password,
            ) = unserialize($serialized, ['allowed_classes' => false]);
    }
}
