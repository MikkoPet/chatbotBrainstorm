<?php

namespace App\DataFixtures;

use App\Entity\Message;
use App\Entity\Room;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    { // Create 10 users
        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setUsername("user$i");
            $user->setEmail("user$i@example.com");
            $user->setPassword($this->passwordHasher->hashPassword($user, "password$i"));
            $users[] = $user;
            $manager->persist($user);
        }

        // Create 5 rooms
        $rooms = [];
        for ($i = 1; $i <= 5; $i++) {
            $room = new Room();
            $room->setTitle("Room $i");
            $rooms[] = $room;
            $manager->persist($room);
        }

        // Add users to rooms and create messages
        foreach ($users as $user) {
            // Add user to 2-3 random rooms
            $numRooms      = rand(2, 3);
            $shuffledRooms = $rooms;
            shuffle($shuffledRooms);

            for ($i = 0; $i < $numRooms; $i++) {
                $user->addRoom($shuffledRooms[$i]);

                // Create 5-10 messages for this user in this room
                $numMessages = rand(5, 10);
                for ($j = 0; $j < $numMessages; $j++) {
                    $message = new Message();
                    $message->setContent("Message " . ($j + 1) . " from " . $user->getUsername() . " in " . $shuffledRooms[$i]->getTitle());
                    $message->setUser($user);
                    $message->setRoom($shuffledRooms[$i]);
                    $manager->persist($message);
                }
            }
        }

        $manager->flush();
    }
}
