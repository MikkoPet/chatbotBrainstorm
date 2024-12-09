<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    /**
     * Displays a specific chat room and its messages.
     */
    #[Route('/room/{id}', name: 'app_room_show')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(Room $room, MessageRepository $messageRepository): Response
    {
        $messages = $messageRepository->findBy(['room' => $room], ['datetime' => 'ASC']);

        return $this->render('room/show.html.twig', [
            'room'     => $room,
            'messages' => $messages,
        ]);
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

        // Send a real-time update to subscribed clients.
        $update = new Update(
            sprintf('/room/%d', $room->getId()),
            json_encode([
                'id'       => $message->getId(),
                'content'  => $message->getContent(),
                'user'     => $message->getUser()->getEmail(),
                'datetime' => $message->getDatetime()->format('Y-m-d H:i:s'),
            ])
        );

        $hub->publish($update);

        return $this->json([
            'id'       => $message->getId(),
            'content'  => $message->getContent(),
            'user'     => $message->getUser()->getEmail(),
            'datetime' => $message->getDatetime()->format('Y-m-d H:i:s'),
        ]);
    }
}