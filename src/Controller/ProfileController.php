<?php

namespace App\Controller;

use App\Entity\Favorite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;

final class ProfileController extends AbstractController
{
    #[Route('/profile/{id?}', name: 'app_profile')]
    public function index(?int $id, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();

        if ($id == NULL) {
            $user = $currentUser;
            if (!$user) {
                return $this->redirectToRoute('app_login');
            }
        } else {
            $user = $em->getRepository(User::class)->find($id);
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