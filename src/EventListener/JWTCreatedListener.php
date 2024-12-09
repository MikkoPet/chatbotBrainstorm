<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $user    = $event->getUser();
        $payload = $event->getData();
        
        // Add user ID to the token payload
        $payload['id'] = $user->getId();

        $event->setData($payload);
    }
}