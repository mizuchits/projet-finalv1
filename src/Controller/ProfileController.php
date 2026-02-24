<?php

namespace App\Controller;

use App\Entity\Favorite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $targetUser = $user;
        $isOwnProfile = true;

        $favorites = $em->getRepository(Favorite::class)->findBy(
            ['user' => $user]
        );

        return $this->render('profile/index.html.twig', [
            'targetUser' => $targetUser,
            'favorites' => $favorites,
            'isOwnProfile' => $isOwnProfile,
            'currentUser' => $user,
        ]);
    }


}