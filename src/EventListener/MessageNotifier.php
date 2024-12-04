<?php

namespace App\EventListener;

use App\Entity\Message;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MessageNotifier
{
    private $hub;

    public function __construct(HubInterface $hub)
    {
        $this->hub = $hub;
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Message) {
            return;
        }

        $update = new Update(
            'https://xixat.cn/rooms/' . $entity->getRoom()->getId(),
            json_encode([$entity])
        );

        $this->hub->publish($update);
    }
}