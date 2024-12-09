<?php

namespace App\Controller;

use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(RoomRepository $roomRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $rooms = $roomRepository->findByUser($user);

        return $this->render('home/index.html.twig', [
            'user'  => $user,
            'rooms' => $rooms,
        ]);
    }
}
