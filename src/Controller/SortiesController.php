<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortiesController extends AbstractController
{
    #[Route('/sorties', name: 'app_sorties')]
    public function indexSorties(): Response
    {
        return $this->render('sorties/index.html.twig', [
            'controller_name' => 'SortiesController',
        ]);
    }

    #[Route('/sortie-create', name: 'sortie_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $sortie = new Sortie();

        $form = $this->createForm(SortiesController::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Publié par défaut
            $sortie->setIsPublished(true);
            // Date = Now
            $sortie->setDateCreated(new \DateTimeImmutable("now"));

            $em->persist($sortie);
            $em->flush();

            $this->addFlash("success", "Sortie Cree");

            return $this->redirectToRoute('', ['id' => $wish->getId()]);
        }

        //TODO  CREER FICHIER TWIG

        return $this->render('sorties/create.html.twig', [
            'wishForm' => $form->createView(),
        ]);
    }
}
