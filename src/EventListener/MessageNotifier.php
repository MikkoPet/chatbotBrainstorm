<?php

namespace App\EventListener;

use App\Entity\Message;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Psr\Log\LoggerInterface;

class MessageNotifier
{
    private $hub;
    private $logger;

    public function __construct(HubInterface $hub, LoggerInterface $logger)
    {
        $this->hub = $hub;
        $this->logger = $logger;
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->logger->info('MessageNotifier: postPersist event triggered');
        $entity = $args->getObject();

        if (!$entity instanceof Message) {
            $this->logger->info('MessageNotifier: Entity is not a Message instance');
            return;
        }

        $this->logger->info('MessageNotifier: Processing Message entity', [
            'message_id' => $entity->getId(),
            'room_id' => $entity->getRoom()->getId(),
        ]);

        try {
            $update = new Update(
                'https://xixat.cn/rooms/' . $entity->getRoom()->getId(),
                json_encode([
                    'id' => $entity->getId(),
                    'content' => $entity->getContent(),
                    'datetime' => $entity->getDatetime()->format('c'),
                    'user' => [
                        'id' => $entity->getUser()->getId(),
                        'username' => $entity->getUser()->getUsername(),
                    ],
                ])
            );

            $this->logger->info('MessageNotifier: Created Update object', [
                'topic' => $update->getTopics()[0],
                'data' => $update->getData(),
            ]);
            $this->hub->publish($update);

            $this->logger->info('MessageNotifier: Published update to Mercure hub');
        } catch (\Exception $e) {
            $this->logger->error('MessageNotifier: Error publishing to Mercure hub', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}