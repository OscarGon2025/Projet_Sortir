<?php

namespace App\Controller;


use App\Entity\Site;
use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\FiltreSiteType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Client\Curl\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
//    #[Route('/sorties', name: 'app_sorties')]
//    public function indexSorties(): Response
//    {
//        return $this->render('sorties/list.html.twig', [
//            'controller_name' => 'SortieController',
//        ]);
//    }

    #[Route('/sortie-create', name: 'sortie_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $sortie = new Sortie();

        // Assigner l’organisateur connecté
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour créer une sortie.');
        }
        $sortie->setOrganisateur($user);

        $form = $this->createForm(SortieType::class, $sortie);

        $form->handleRequest($request);

        if (!$sortie->getDateCreated()) {
            $sortie->setDateCreated(new \DateTimeImmutable());
        }


        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($sortie);
            $em->flush();

            $this->addFlash("success", "Sortie créée.");

            return $this->redirectToRoute('app_sortie', ['id' => $sortie->getId()]);
        }

        return $this->render('sorties/create_sortie.html.twig', [
            'sortieForm' => $form->createView(),
        ]);
    }

    #[Route('/sortie-list', name: 'app_sortie', methods: ['GET', 'POST'])]
    public function list(Request $request, SortieRepository $sortieRepository): Response
    {
        $formFiltre = $this->createForm(FiltreSiteType::class);
        $formFiltre->handleRequest($request);

        $site = null;
        if ($formFiltre->isSubmitted() && $formFiltre->isValid()) {
            $site = $formFiltre->get('site')->getData();
        }

        $sorties = $sortieRepository->findBySite($site ? $site->getId() : null);

        return $this->render('sorties/list.html.twig', [
            'sorties' => $sorties,
            'formFiltre' => $formFiltre->createView(),
        ]);
    }


    #[Route('/sortie/{id}', name: 'sortie_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $sortie = $em->getRepository(Sortie::class)->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException("Sortie non trouvée.");
        }

        $now = new \DateTimeImmutable();

        // Considérons qu'une sortie est clôturée si son état est "Clôturée"
        // ou si la date de début est passée
        $estCloturee = false;
        if (strtolower($sortie->getEtat()->getLibelle()) === 'clôturée' || strtolower($sortie->getEtat()->getLibelle()) === 'cloturée') {
            $estCloturee = true;
        } elseif ($sortie->getDateHeureDebut() < $now) {
            $estCloturee = true;
        }

        return $this->render('sorties/show.html.twig', [
            'sortie' => $sortie,
            'estCloturee' => $estCloturee,
        ]);
    }

    #[Route('/sortie/{id}/inscription', name: 'sortie_inscription', methods: ['GET', 'POST'])]
    public function inscription(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour vous inscrire à une sortie.');
        }

        $sortie = $em->getRepository(Sortie::class)->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException("La sortie n'existe pas ou n'est pas trouvée.");
        }

        $maintenant = new \DateTimeImmutable();


        // Condition 1 : La sortie doit être ouverte
        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {

            $this->addFlash('error', "Cette sortie n'est pas ouverte aux inscriptions.");
            return $this->redirectToRoute('app_sortie');
        }

        // Condition 2 : La date limite ne doit pas être dépassée
        if ($sortie->getDateLimiteInscription() < $maintenant) {
            $this->addFlash('error', "La date limite d'inscription est dépassée.");
            return $this->redirectToRoute('app_sortie');
        }

        // Condition 3 : L'utilisateur n'est pas déjà inscrit
        if ($sortie->getUsers()->contains($user)) {
            $this->addFlash('warning', "Vous êtes déjà inscrit à cette sortie.");
            return $this->redirectToRoute('app_sortie');
        }

        // Inscription
        $sortie->addUser($user);
        $em->persist($sortie);
        $em->flush();

        $this->addFlash('success', 'Inscription réussie à la sortie !');
        return $this->redirectToRoute('app_sortie');
    }

    #[Route('/sortie/{id}/desinscription', name: 'sortie_desinscription', methods: ['POST'])]
    public function desinscription(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour vous désinscrire.');
        }

        $sortie = $em->getRepository(Sortie::class)->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée.');
        }

        //Empêcher le user de se désinscrire alors que la sortie a déjà commencé.
        $maintenant = new \DateTimeImmutable();
        if($sortie->getDateHeureDebut() <= $maintenant) {
            $this->addFlash('error' , 'La sortie a déjà commencé, vous ne pouvez plus vous désinscrire.');
            return $this->redirectToRoute('sortie_show', ['id' => $id]);
        }

        //Pour vérifier que le user est bien inscrit à la sortie.
        if (!$sortie->getUsers()->contains($user)) {
            $this->addFlash('warning', "Vous n'êtes pas inscrit à cette sortie.");
            return $this->redirectToRoute('sortie_show', ['id' => $id]);
        }

        $sortie->removeUser($user);
        $em->persist($sortie);
        $em->flush();

        $this->addFlash('success', 'Vous êtes désinscrit de la sortie.');
        return $this->redirectToRoute('sortie_show', ['id' => $id]);

    }

    #[Route('/historique-sortie', name: 'historique_sortie', methods: ['GET'])]
    public function historique (SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findSortiesArchivees();

        return $this->render('sorties/historique.html.twig', [ 'sorties' => $sorties
        ]);

    }

}

