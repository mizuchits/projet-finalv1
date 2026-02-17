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
    public function __construct(private OsuapiService $osuApi) {}

    #[Route('', name: 'beatmap_search')]
    public function search(Request $request): Response
    {
        $query = $request->query->get('q', '');
        $beatmaps = [];

        if ($query) {
            $beatmaps = $this->osuApi->searchBeatmaps($query, 20);
            
        }

        return $this->render('beatmap/search.html.twig', [
            'beatmaps' => $beatmaps,
            'query' => $query,
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
}
