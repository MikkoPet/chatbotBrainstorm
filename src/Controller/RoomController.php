<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\Message;
use App\Form\RoomType;
use App\Repository\MessageRepository;
use CoopTilleuls\UrlSignerBundle\UrlSigner\UrlSignerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

/**
 * Controller for handling room-related actions.
 */
class RoomController extends AbstractController
{
    private $logger;
    private $urlSigner;

    public function __construct(LoggerInterface $logger, UrlSignerInterface $urlSigner)
    {
        $this->logger = $logger;
        $this->urlSigner = $urlSigner;
    }

    /**
     * Displays a specific chat room and its messages.
     */
    #[Route('/room/{id}', name: 'app_room_show')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(Room $room, MessageRepository $messageRepository, JWTTokenManagerInterface $jwtManager, HubInterface $hub): Response
    {
        $messages = $messageRepository->findBy(['room' => $room], ['datetime' => 'ASC']);

        $response = $this->render('room/show.html.twig', [
            'room'          => $room,
            'messages'      => $messages,
            'mercureHubUrl' => $hub->getPublicUrl(),
            'roomTopic'     => sprintf('room/%d', $room->getId()),
        ]);

        return $response;
    }

    /**
     * Handles sending a new message in a chat room.
     * It first creates a new message object, persists it to the database,
     * and then sends a real-time update to the subscribed clients via mercure..
     */
    #[Route('/room/{id}/send', name: 'app_room_send_message', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function sendMessage(Request $request, Room $room, EntityManagerInterface $entityManager, HubInterface $hub): Response
    {
        $content = json_decode($request->getContent(), true)['content'] ?? null;
        if (!$content) {
            return $this->json(['error' => 'Message content is required'], 400);
        }

        // Create and persist the new message.
        $message = new Message();
        $message->setContent($content);
        $message->setUser($this->getUser());
        $message->setRoom($room);
        $message->setDatetime(new \DateTimeImmutable());

        $entityManager->persist($message);
        $entityManager->flush();

        // Prepare the message data
        $messageData = [
            'id'       => $message->getId(),
            'content'  => $message->getContent(),
            'user'     => $message->getUser()->getUsername(),
            'datetime' => $message->getDatetime()->format('d-m-Y H:i'),
        ];
        // Send a real-time update to subscribed clients.
        $topic  = sprintf('room/%d', $room->getId());
        $update = new Update(
            $topic,
            json_encode($messageData)
        );

        $hub->publish($update);

        // Log the Mercure update
        $this->logger->info('Mercure update: Sent message to topic "{topic}"', ['topic' => $topic]);

        return $this->json([
            'id'       => $message->getId(),
            'content'  => $message->getContent(),
            'user'     => $message->getUser()->getUsername(),
            'datetime' => $message->getDatetime()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Displays a form to create a new chat room.
     */
    #[Route('/room/create', name: 'app_room_create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($room);
            $entityManager->flush();

            $this->addFlash('success', 'Room created successfully.');
            return $this->redirectToRoute('app_room_show', ['id' => $room->getId()]);
        }

        return $this->render('room/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Handles adding a user to a specific chat room via signed invite link.
     */
    #[Route('/room/invite/{id}', name: 'app_room_invite', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function invite(Room $room, EntityManagerInterface $entityManager): RedirectResponse
    {
        $user = $this->getUser();
        $room->addUser($user);
        $entityManager->flush();
        // Redirect to the room after successful invite
        return $this->redirectToRoute('app_room_show', ['id' => $room->getId()]);
    }

    /**
     * Handles generating and returning a signed invite link for a specific chat room.
     */
    #[Route('/room/{id}/generate', name: 'app_room_generate', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function generateInviteLink(Room $room): JsonResponse
    {
        $this->logger->info('Generating invite link for room {room}', ['room' => $room->getId()]);
        try {
            $url = $this->generateUrl('app_room_invite', ['id' => $room->getId()], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
            $expiration = (new \DateTime('now'))->add(new \DateInterval('P1D'));
            $signedUrl = $this->urlSigner->sign($url, $expiration);

            return $this->json(['inviteLink' => $signedUrl]);
        } catch (\Exception $e) {
            $this->logger->error('Error generating invite link: ' . $e->getMessage());
            return $this->json(['error' => 'An error occurred while generating the invite link'], 500);
        }
    }

}