<?php

namespace App\Entity;

use App\Repository\InvitationRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=InvitationRepository::class)
 */
class Invitation
{
    /**
     * @todo BETTER TO USE ENUM TYPE - NOW I DON'T HAVE TIME
     */
    const STATUS_CODE_CANCELLED = 0;  // Sender can cancel
    const STATUS_CODE_SENT      = 1;  // Sender can accept
    const STATUS_CODE_ACCEPTED  = 2;  // Guest can accept
    const STATUS_CODE_DECLINED  = 3;  // Guest can decline
    const ERROR_MESSAGE_401_UNAUTHORIZED = 'You are not Authenticated';
    const ERROR_MESSAGE_403_FORBIDDEN = 'Access Forbidden, You are not authorize to access';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="invitations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sender;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $guestEmail;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): self
    {
        \Assert\Assert::that($status)->inArray([
            self::STATUS_CODE_ACCEPTED,
            self::STATUS_CODE_DECLINED,
            self::STATUS_CODE_CANCELLED,
            self::STATUS_CODE_SENT,
        ], 'Invalid Status Code');

        $this->status = $status;
        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeInterface $createdAt
     * @return $this
     */
    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return User
     */
    public function getSender(): User
    {
        return $this->sender;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setSender($sender): self
    {
        $this->sender = $sender;
        return $this;
    }


    /**
     * @return string
     */
    public function getGuestEmail(): string
    {
        return $this->guestEmail;
    }

    /**
     * @param string $guestEmail
     * @return $this
     */
    public function setGuestEmail(string $guestEmail): self
    {
        $this->guestEmail = $guestEmail;
        return $this;
    }
}
