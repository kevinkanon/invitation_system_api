<?php

namespace App\DataFixtures;

use App\Entity\Invitation;
use App\Entity\User;
use App\Repository\InvitationRepositoryInterface;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;
    /**
     * @var InvitationRepositoryInterface
     */
    private $invitationRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(
        UserPasswordEncoderInterface $encoder,
        InvitationRepositoryInterface $invitationRepository,
        UserRepository $userRepository,
        EntityManagerInterface $manager
    )
    {
        $this->encoder = $encoder;
        $this->invitationRepository = $invitationRepository;
        $this->userRepository = $userRepository;
        $this->manager = $manager;
    }

    public function load( ObjectManager $manager): void
    {
       $this->loadUsers();

        $faker = \Faker\Factory::create('fr_FR');
        $users = $this->userRepository->findAll();
        for ($i = 0; $i <= 50; $i++) {
            $sender = $faker->randomElement($users);
            $receiver = $faker->randomElement($users);
            if ($this->canBuildInvitationFixtures($sender, $receiver)) {
                $invitation = new Invitation();
                $invitation->setSender($sender)
                            ->setCreatedAt($faker->dateTime())
                            ->setStatus(
                                $faker->numberBetween( // betWeen here is about min value and max integers
                                Invitation::STATUS_CODE_CANCELLED,
                                Invitation::STATUS_CODE_DECLINED
                                )
                            )
                            ->setGuestEmail($receiver->getEmail());

                $manager->persist($invitation);
            }
        }
        $manager->flush();
    }

    private function loadUsers()
    {
        $faker = \Faker\Factory::create('fr_FR');
        for ($i = 0; $i <= 10; $i++) {
            $user = new User();
            $password = $this->encoder->encodePassword($user, $faker->password());
            $user->setUsername($faker->userName)
                ->setPassword($password)
                ->setEmail($faker->email())
                ->setEnabled(1);

            $this->manager->persist($user);
        }
        $this->manager->flush();
    }

    /**
     * @param User $sender
     * @param User $receiver
     * @return bool
     */
    public function canBuildInvitationFixtures(User $sender, User $receiver): bool
    {
        return ($sender->getId() !== $receiver->getId())
            &&
            ($sender->getEmail() !== $receiver->getEmail());
    }

    /**
     * number in which order to load fixtures
     *
     * @return int
     */
    public function getOrder(): int
    {
        return 1;
    }
}
