<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;


final class AdminController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'app_admin')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/desactiverUsers.html.twig', [
            'users' => $users,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/users/desactivate', name: 'admin_users_desactiver', methods: ['POST'])]
    public function desactiverUsers(Request $request, UserRepository $userRepository
    ): Response {
        $ids = $request->request->all('user_ids'); // tableau d’IDs sélectionnés

        if (!empty($ids)) {
            $count = $userRepository->desactiverUsers($ids);
            $this->addFlash('success', "$count utilisateurs désactivés avec succès.");
        } else {
            $this->addFlash('warning', "Aucun utilisateur sélectionné.");
        }

        return $this->redirectToRoute('app_admin');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/users/delete', name: 'admin_users_delete', methods: ['POST'])]
    public function supprimerUsers(Request $request, UserRepository $userRepository): Response
    {
        $ids = (array) $request->request->all('user_ids'); // récupère le tableau d'IDs

        if (!empty($ids)) {
            $count = $userRepository->supprimerUsers($ids);
            $this->addFlash('success', "$count utilisateurs supprimés avec succès.");
        } else {
            $this->addFlash('warning', "Aucun utilisateur sélectionné.");
        }

        return $this->redirectToRoute('app_admin');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/users/reactivate', name: 'admin_users_reactivate', methods: ['POST'])]
    public function reactiverUsers(Request $request, UserRepository $userRepository): Response
    {
        $ids = (array) $request->request->all('user_ids'); // récupère le tableau d'IDs

        if (!empty($ids)) {
            $count = $userRepository->reactiverUsers($ids);
            $this->addFlash('success', "$count utilisateurs réactivés avec succès.");
        } else {
            $this->addFlash('warning', "Aucun utilisateur sélectionné.");
        }

        return $this->redirectToRoute('app_admin');
    }


}
