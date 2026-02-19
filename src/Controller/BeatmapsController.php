<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Service\OsuapiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/beatmaps')]
class BeatmapsController extends AbstractController
{
    public function __construct(private OsuapiService $osuApi)
    {
    }

    #[Route('', name: 'beatmap_search')]
    public function search(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $query = $request->query->get('q', '');
        $beatmaps = [];

        $favoriteIds = [];
        if ($user) {
            $favorites = $em->getRepository(Favorite::class)->findBy(['user' => $user]);
            $favoriteIds = array_map(fn($fav) => $fav->getBeatmapsetId(), $favorites);
        }

        if (!$query) {
            $beatmaps = $this->osuApi->searchBeatmaps('', 20);
        } else {
            $beatmaps = $this->osuApi->searchBeatmaps($query, 20);
        }

        return $this->render('beatmap/search.html.twig', [
            'beatmaps' => $beatmaps,
            'query' => $query,
            'user' => $user,
            'favoriteIds' => $favoriteIds,
        ]);
    }

    #[Route('/favorite/add/{beatmapsetId}', name: 'favorite_add', methods: ['POST'])]
    public function addFavorite(int $beatmapsetId, EntityManagerInterface $em): JsonResponse
    {

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Not logged in'], 401);
        }
        $existing = $em->getRepository(Favorite::class)->findOneBy([
            'user' => $user,
            'beatmapsetId' => $beatmapsetId,
        ]);

        if ($existing) {
            return $this->json(['already' => true]);
        }


        $setInfo = $this->osuApi->getBeatmapset($beatmapsetId);
        if (!$setInfo) {
            return $this->json(['error' => 'Beatmapset not found'], 404);
        }


        $fav = new Favorite();
        $fav->setUser($user);
        $fav->setBeatmapsetId($beatmapsetId);
        $fav->setTitle($setInfo['title'] ?? 'Unknown');
        $fav->setArtist($setInfo['artist'] ?? 'Unknown');
        $fav->setCoverUrl("https://assets.ppy.sh/beatmaps/{$beatmapsetId}/covers/cover.jpg");

        $em->persist($fav);
        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/Favorite/{id}/delete', name: 'app_delete_favorite')]
    public function delete(Favorite $favorite, EntityManagerInterface $entityManager): Response
    {

        if ($favorite->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette Favorite.');
        }

        $entityManager->remove($favorite);
        $entityManager->flush();

        $this->addFlash('success', 'Favorite supprimée avec succès !');
        return $this->redirectToRoute('app_profile');
    }
}
