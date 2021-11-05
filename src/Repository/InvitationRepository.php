<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Invitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Invitation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invitation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invitation[]    findAll()
 * @method Invitation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitationRepository extends ServiceEntityRepository implements InvitationRepositoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Invitation::class);
        $this->entityManager = $entityManager;
    }

    /**
     * @param Invitation $invitation
     */
    public function save(Invitation $invitation): void
    {
        $this->entityManager->persist($invitation);
        $this->entityManager->flush();
    }

    /**
     * @param int $id
     * @return Invitation|null
     */
    public function findOneById(int $id): Invitation
    {
        return $this->findOneBy([
            'id' => $id
        ]);
    }

    /**
     * @param User $user
     * @return Invitation[]
     */
    public function findSenderInvitations(User $user): array
    {
        return $this->findBy([
            'sender' => $user
        ]);
    }

    /**
     * @param string $guestEmail
     * @return Invitation[]
     */
    public function findGuestInvitations(string $guestEmail): array
    {
        return $this->findBy([
            'guestEmail' => $guestEmail
        ]);
    }


}
