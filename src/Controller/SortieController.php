<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
    #[Route('/sortie-list', name: 'app_sortie', methods: ['GET'])]
    public function list(sortieRepository $repository): Response
    {
        $sorties = $repository->findAll();
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sorties,
        ]);
    }
}
