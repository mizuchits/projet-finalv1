<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class OsuConnectController extends AbstractController
{
    #[Route('/connect/osu', name: 'connect_osu')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function connect(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry->getClient('osu')->redirect(['public'], []);
    }

    #[Route('/connect/osu/check', name: 'connect_osu_check')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function check(
        Request $request,
        ClientRegistry $clientRegistry,
        EntityManagerInterface $em
    ): Response {
        $client = $clientRegistry->getClient('osu');

        try {
            /** @var \League\OAuth2\Client\Provider\ResourceOwnerInterface $osuUser */
            $osuUser = $client->fetchUser();

            $data = $osuUser->toArray();

            /** @var User $user */
            $user = $this->getUser();

            $user->setOsuId($data['id'] ?? null);
            $user->setOsuUsername($data['username'] ?? null);

            $em->flush();

            $this->addFlash('success', 'Ton compte osu! a été lié avec succès !');

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la liaison : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_profile', ['id' => $this->getUser()->getId()]);
    }
}