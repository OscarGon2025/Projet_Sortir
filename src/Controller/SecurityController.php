<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Form\UserModifFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
//use http\Env\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


final class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        //Si connecté, cela empêche qlqu'un de co de revenir sur /login
        if ($this->getUser()) {
            return $this->redirectToRoute('app_main');
        }

        //Message erreur
        $error = $authenticationUtils->getLastAuthenticationError();
        //Dernier username entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();


        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Vous devez être connecté pour accéder à cette page.');
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        $user = $this->getUser();

        //Renvoie vers la page /login si utilisateur pas connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profile/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function editProfile(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UserModifFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $photoFile */
            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                //Vérifier que le format de l'image est autorisé.
//                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
//                $safeFilename = $slugger->slug($originalFilename);
//                $newFilename = uniqid() . '.' . $photoFile->guessExtension();
                $allowedMimeTypes = ['image/jpeg', 'image/png'];
                if (!in_array($photoFile->getMimeType(), $allowedMimeTypes)) {
                    $this->addFlash('error', 'Format de fichier non autorisé. Veuillez choisir un fichier JPG ou PNG.');
                    return $this->redirectToRoute('app_profile_edit');
                }

                //Vérifier la taille max de l'image.
                if ($photoFile->getSize() > 2 * 1024 * 1024) {
                    $this->addFlash('error', 'Fichier trop volumineux (max 2 Mo).');
                    return $this->redirectToRoute('app_profile_edit');
                }

                //Vérifier le contenu réel de l'image
                $imageCheck = @imagecreatefromstring(file_get_contents($photoFile->getPathname()));


                //Créer un nom de fichier sécurisé
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();


                // Pour supprimer l'ancienne photo si déjà une existante
                $oldPhoto = $user->getPhotoFilename();
                if ($oldPhoto) {
                    $oldPhotoPath = $this->getParameter('photos_directory') . '/' . $oldPhoto;
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }

                //Déplacement du fichier photo dans le dossier prévu
                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', "Erreur lors de l'upload de la photo : " . $e->getMessage());
                    return $this->redirectToRoute('app_profile_edit');
                }


                $user->setPhotoFilename($newFilename);
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès !');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/profile/change-password', name: 'app_change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $form = $this->createForm(ChangePasswordFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            //Hasher le nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Mot de passe modifié avec succès !');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/{id}', name: 'user_profile_public', methods: ['GET'])]
    public function publicProfile(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException("Utilisateur non trouvé.");
        }

        return $this->render('profile/public_profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password')]
    public function forgotPassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository, MailerInterface $mailer): Response
    {

        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneBy(['mail' => $form->get('mail')->getData()]);

            if ($user) {
                //Pour générer un token unique
                $token = bin2hex(random_bytes(32));
                //Mettre le token et sa date d'expiration en BDD
                $expiresAt = new \DateTimeImmutable('+1 hour');
                $user->setResetToken($token);
//                $user->setResetTokenExpiresAt(new \DateTime('+1 hour'));
                $user->setResetTokenExpiresAt($expiresAt);
                $em->flush();

                //Pour générer l'URL
                $url = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                //Pour envoyer l'email
                $email = (new Email())
                    ->from('no-reply@openblog.test')
                    ->to($user->getMail())
                    ->subject('Récupération de mot de passe sur OpenBlog')
                    ->html($this->renderView('emails/password_reset.html.twig', [
                        'user' => $user,
                        'url'  => $url,
                        'expiresAt' => $expiresAt,
                        'validityMinutes' => 60,
                    ]));
                $mailer->send($email);

//                $this->addFlash('success', 'Email envoyé avec succès.');
//                return $this->redirectToRoute('app_login');
                $this->addFlash('success', 'Email envoyé avec succès.');

// TEMPORAIRE : on reste sur la page pour voir la toolbar et le mail dans le profiler
                return $this->render('security/reset_password_request.html.twig', [
                    'requestPassForm' => $form->createView(),
                ]);

            }

            $this->addFlash('danger', 'Un problème est survenu.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm' => $form->createView(),
        ]);
    }

    #[Route('/mot-de-passe-oublie/{token}', name: 'reset_password')]
    public function resetPassword(string $token, UserRepository $userRepository, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        $user = $userRepository->findOneBy(['resetToken' => $token]);

        if (!$user || $user->getResetTokenExpiresAt() < new \DateTimeImmutable()) {
            $this->addFlash('danger', 'Le lien est invalide ou a expiré.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('password')->getData()));

            //Pour invalider le token afin qu'il ne soit plus réutilisable.
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);

            $em->flush();

            $this->addFlash('success', 'Mot de passe changé avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'passForm' => $form->createView(),
        ]);
    }

    #[Route('/test-mail', name: 'app_test_mail')]
    public function testMail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('test@example.com')
            ->to('fake@example.com')
            ->subject('Email de test')
            ->text('Ceci est un test')
            ->html('<p>Ceci est un <b>test</b></p>');

        $mailer->send($email);

        return new Response('Mail envoyé (simulé)');
    }


}
