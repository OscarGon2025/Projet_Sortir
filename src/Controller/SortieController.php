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
        return $this->render('sorties/list.html.twig', [
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
        return $this->render('sortie/list_sortie.html.twig', [
            'sorties' => $sorties,
        ]);
    }


    #[Route('/sortie/{id}/inscription', name: 'sortie_inscription', methods: ['GET', 'POST'])]
    public function inscription(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $sortie = $em->getRepository(Sortie::class)->find($id);

        if(!$sortie) {
            throw $this->createNotFoundException("La sortie n'existe pas ou n'est pas trouvée.");
        }
        $maintenant = new \DateTimeImmutable();

        //Condition 1 : Statut de la sortie doit être "ouverte"
        if ($sortie->getEtat()->getLibelle() === 'Ouverte') {
            $this->addFlash('error', "Cette sortie n'est pas ouverte aux inscriptions.");
            return $this->redirectToRoute('app_sorties');
    }
        //Condition 2 : La date limite ne doit pas être dépassée
        if($sortie->getDateLimiteInscription() < $maintenant) {
            $this->addFlash('error', "La date limite d'inscription est dépassée.");
            return $this->redirectToRoute('app_sorties');
        }

        // Conditions remplies pour l'inscription : ManyToMany entre User et Sortie
        $sortie->addUser($user);
        $em->persist($sortie);
        $em->flush();

        $this->addFlash('success', 'Inscription réussie à la sortie !');
        return $this->redirectToRoute('app_sorties');
    }
}
