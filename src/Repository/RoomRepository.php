<?php

namespace App\Repository;

use App\Entity\Room;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Room>
 */
class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    /**
     * @return Room[] Returns an array of Room objects
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere(':user MEMBER OF r.users')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
