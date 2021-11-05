<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\InvitationRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Invitation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InvitationController extends AbstractController
{
    /**
     * @var User|null
     */
    private $user;

    public function __construct()
    {
        $this->user = $this->getUser();
    }

    /**
     * @todo ADD A PAGINATOR
     *
     * @Route("/api/v1/invitations/sent", name="api_invitaion_sent", methods={"GET"})
     * @param Request $request
     * @param InvitationRepositoryInterface $invitationRepository
     * @return JsonResponse
     */
    public function listSentInvitationsAction(Request $request, InvitationRepositoryInterface $invitationRepository)
    {
       // A PAGINATOR CAN BE ADD HERE
        return $this->isUserAuthenticated() ?
            $this->json(
               ['results' => $invitationRepository->findSenderInvitations($this->user)],
                Response::HTTP_OK
            )
            :
            $this->json(
                ['errors' => Invitation::ERROR_MESSAGE_401_UNAUTHORIZED],
                Response::HTTP_UNAUTHORIZED
            );
    }

    /**
     * @todo ADD A PAGINATOR
     *
     * @Route("/api/v1/invitations/received", name="api_invitaion_received", methods={"GET"})
     * @param Request $request
     * @param InvitationRepositoryInterface $invitationRepository
     * @return JsonResponse
     */
    public function listReceivedInvitationsAction(Request $request, InvitationRepositoryInterface $invitationRepository)
    {
        // A PAGINATOR CAN BE ADD HERE
        return $this->isUserAuthenticated() ?
            $this->json(
               ['results' => $invitationRepository->findGuestInvitations($this->user->getEmail())],
                Response::HTTP_OK
            )
            :
            $this->json(
                ['errors' => Invitation::ERROR_MESSAGE_401_UNAUTHORIZED],
                Response::HTTP_UNAUTHORIZED
            );
    }

    /**
     * @Route("/api/v1/invitations/create", name="api_invitaion_create", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param InvitationRepositoryInterface $invitationRepository
     * @return JsonResponse
     * @throws \Exception
     */
    public function newInvitationAction(
        Request $request,
        ValidatorInterface $validator,
        InvitationRepositoryInterface $invitationRepository
    )
    {
        if ($this->isUserAuthenticated()) {
            $data = json_decode($request->getContent(), true);
            $invitation = new Invitation();
            $invitation->setSender($this->user);
            $invitation->setGuestEmail($data('guestEmail'));
            $invitation->setStatus(Invitation::STATUS_CODE_SENT);
            $invitation->setCreatedAt(new \DateTime());
            $errors = $validator->validate($invitation);
            if (0 > count($errors)) {
                return $this->json(
                    ['errors' => (string) $errors],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $invitationRepository->save($invitation);

            return $this->json(
                [
                    'id' => (int) $invitation->getId(),
                    'message' => 'Invitation created successfully',
                ],
                Response::HTTP_CREATED
            );
        } else {
            return $this->json([], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Change Status Code only to Cancel  ## no Delete method exist
     *
     * @Route("/api/v1/invitations/cancel/{id}", name="api_invitation_cancel", methods={"PUT"}, requirements={"id"="\d+"})
     * @param int $id
     * @param InvitationRepositoryInterface $invitationRepository
     * @return JsonResponse
     */
    public function cancelInvitationAction(
        int $id,
        InvitationRepositoryInterface $invitationRepository
    )
    {
        if ($this->isUserAuthenticated()) {
            $invitation = $invitationRepository->findOneById($id);
            if ($this->isSender($invitation)) {
                $this->updateStatusCode($invitation,Invitation::STATUS_CODE_CANCELLED, $invitationRepository);

                return $this->json(['message' => 'Cancelled Successfully'],Response::HTTP_OK);
            } else {
                return $this->json(['errors' => Invitation::ERROR_MESSAGE_403_FORBIDDEN],Response::HTTP_FORBIDDEN);
            }
        } else {
            return $this->json(['errors' => Invitation::ERROR_MESSAGE_401_UNAUTHORIZED], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @Route("/api/v1/invitations/{id}/status/{statusCode}", name="api_invitaion_accept_decline", methods={"PUT"}, requirements={"statusCode"="\d+"})
     * @param int $id
     * @param int $statusCode
     * @param InvitationRepositoryInterface $invitationRepository
     * @return JsonResponse
     */
    public function acceptOrDeclineInvitationAction(
        int $id,
        int $statusCode,
        InvitationRepositoryInterface $invitationRepository
    )
    {
        if ($this->isUserAuthenticated()) {
            $invitation = $invitationRepository->findOneById($id);
            if ($this->isGuest($invitation)) {
                $error = !in_array($statusCode, [Invitation::STATUS_CODE_ACCEPTED, Invitation::STATUS_CODE_DECLINED]) ?
                    [$statusCode . ' is not a valid status code.']
                    :
                    [];
                if (empty($error))
                    $this->updateStatusCode($invitation, $statusCode, $invitationRepository) ;

                $response = !empty($error) ?
                    $this->json(['errors' => (string) $error], Response::HTTP_BAD_REQUEST)
                    :
                    $this->json(['message' => 'Updated successfully'],Response::HTTP_OK);
            } else {
                $response = $this->json(['message' => Invitation::ERROR_MESSAGE_403_FORBIDDEN], Response::HTTP_FORBIDDEN);
            }
        } else {
            $response = $this->json(['message' => Invitation::ERROR_MESSAGE_401_UNAUTHORIZED], Response::HTTP_UNAUTHORIZED);
        }
        return $response;
    }

    /**
     * @todo CAN BE REFACO TO AN SERVICE
     *
     * @return bool
     */
    private function isUserAuthenticated(): bool
    {
        return null !== $this->user;
    }

    /**
     * @todo CAN BE REFACO TO AN SERVICE
     *
     * @param Invitation $invitation
     * @return bool
     */
    private function isSender(Invitation $invitation): bool
    {
        return (null !== $invitation && null !== $this->user)
                &&
                ($this->user->getId() === $invitation->getSender()->getId());
    }

    /**
     * @todo CAN BE REFACO TO AN SERVICE
     *
     * @param Invitation $invitation
     * @return bool
     */
    private function isGuest(Invitation $invitation): bool
    {
        return (null !== $invitation && null !== $this->user)
                &&
                ($this->user->getEmail() === $invitation->getGuestEmail());

    }

    /**
     * @todo CAN BE REFACO TO AN SERVICE
     *
     * @param Invitation $invitation
     * @param int $statusCode
     * @param InvitationRepositoryInterface $invitationRepository
     */
    public function updateStatusCode(
        Invitation $invitation,
        int $statusCode,
        InvitationRepositoryInterface $invitationRepository
    ): void
    {
        $invitation->setStatus($statusCode);
        $invitationRepository->save($invitation);
    }
}