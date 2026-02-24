<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Form\EditBeatmapType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EditBeatmapController extends AbstractController
{
    #[Route('/edit/beatmap/{id}', name: 'app_edit_beatmap')]
    public function index(Favorite $favorite, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();


        $form = $this->createForm(EditBeatmapType::class, $favorite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'beatmap mis à jour avec succès !');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
