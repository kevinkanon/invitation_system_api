<?php

namespace App\Repository;

use App\Entity\Invitation;
use App\Entity\User;

interface InvitationRepositoryInterface
{

    /**
     * @param Invitation $invitation
     */
    public function save(Invitation $invitation): void;

    /**
     * @param int $id
     * @return Invitation|null
     */
    public function findOneById(int $id): Invitation;

    /**
     * @param User $user
     * @return Invitation[]
     */
    public function findSenderInvitations(User $user): array;

    /**
     * @param string $guestEmail
     * @return Invitation[]
     */
    public function findGuestInvitations(string $guestEmail): array;
}