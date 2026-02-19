<?php

namespace App\Controller;

use App\Entity\Favorite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();



        if (!$user) {
            throw $this->createAccessDeniedException('Connectez-vous pour voir votre profil.');
        }

        $favorites = $em->getRepository(Favorite::class)->findBy(
            ['user' => $user]
        );

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'favorites' => $favorites,
        ]);
    }
}