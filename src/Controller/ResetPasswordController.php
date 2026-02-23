<?php

namespace App\Controller;

use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final class ResetPasswordController extends AbstractController
{
    #[Route('/reset/password', name: 'app_forgot_password')]
    public function request(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class); //Création du formulaire
        $form->handleRequest($request); //Récupération et validation de la requête
        
        if ($form->isSubmitted() && $form->isValid()) {
            //Récupération du champ email dans une variable
            $email = $form->get('email')->getData(); //Récupération du champ email dans une variable
            $user = $userRepository->findOneBy(['email' => $email]); //Requete DQL pour récupérer l'objet utilisateur associé

            if ($user) { //Vérification si l'utilisateur existe
                $token = Uuid::v4()->toRfc4122(); //Uuid = Universally Unique Identifier permet de créer un Token unique
                $user->setResetToken($token); //On stocke le token dans l'objet user
                $user->setResetTokenExpiresAt((new \DateTime())->modify('+1 hour')); //On stocke la date d'expiration du token dans l'objet user
                $entityManager->flush(); //Envoi en base de données

                $resetLink = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL); //Création du lien intégrant le token
                $email = (new Email())
                    ->from('noreply@yourdomain.com')
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->text('Voici votre lien de réinitialisation : ' . $resetLink);
                $mailer->send($email);
                $this->addFlash('success', 'Un email de réinitialisation a été envoyé.');
                return $this->redirectToRoute('app_login');
            }
            $this->addFlash('error', 'Aucun utilisateur trouvé pour cet email.');
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(string $token, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->findOneBy(['resetToken' => $token]);

        if (!$user || !$user->isResetTokenValid()) {
            $this->addFlash('danger','Ce lien de réinitialisation est invalide ou expiré.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('plainPassword')->getData();
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $user->setPassword($hashedPassword);
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset_password.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
