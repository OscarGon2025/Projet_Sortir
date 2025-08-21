<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\UserModifFormType;
use Doctrine\ORM\EntityManagerInterface;
//use http\Env\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

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
        if (!$user){
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profile/profile.html.twig',[
            'user' => $user,
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function editProfile(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();

        if(!$user){
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
                    }
                    catch (FileException $e) {
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

        if(!$user){
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

            $this->addFlash('success' , 'Mot de passe modifié avec succès !');

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

}
