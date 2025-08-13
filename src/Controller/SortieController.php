<?php

namespace App\Controller;


use App\Entity\Site;
use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
    #[Route('/sorties', name: 'app_sorties')]
    public function indexSorties(): Response
    {
        return $this->render('sorties/index.html.twig', [
            'controller_name' => 'SortieController',
        ]);
    }

    #[Route('/sortie-create', name: 'sortie_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $sortie = new Sortie();

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if (!$sortie->getDateCreated()) {
            $sortie->setDateCreated(new \DateTimeImmutable());
        }


        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($sortie);
            $em->flush();

            $this->addFlash("success", "Sortie créée.");

            return $this->redirectToRoute('app_main', ['id' => $sortie->getId()]);
        }

        return $this->render('sorties/create_sortie.html.twig', [
            'sortieForm' => $form->createView(),
        ]);
    }

    #[Route('/sortie-list', name: 'app_sortie', methods: ['GET'])]
    public function list(sortieRepository $repository): Response
    {
        $sorties = $repository->findAll();
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sorties,
        ]);
    }




}
