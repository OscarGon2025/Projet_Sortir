<?php

namespace App\Controller;


use App\Entity\Site;
use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\AnnulationType;
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
        $user = $this->getUser();

        if (!$sortie) {
            throw $this->createNotFoundException("Sortie non trouvée.");
        }

        // Vérifier si l'utilisateur est inscrit
        $inscrit = false;
        if ($user && $sortie->getUsers()->contains($user)) {
            $inscrit = true;
        }

        // Déterminer si la sortie est clôturée
        $now = new \DateTimeImmutable();
        $etat = strtolower($sortie->getEtat()->getLibelle());
        $estCloturee = $etat === 'clôturée' || $etat === 'cloturée' || $sortie->getDateHeureDebut() < $now;

        return $this->render('sorties/show.html.twig', [
            'sortie' => $sortie,
            'estCloturee' => $estCloturee,
            'inscrit' => $inscrit,
        ]);
    }

    #[Route('/sortie/{id}/annuler', name: 'sortie_annuler', methods: ['POST'])]
    public function annuler(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour annuler une sortie.');
        }

        $sortie = $em->getRepository(Sortie::class)->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée.');
        }

        // Vérifie que l'utilisateur est bien l'organisateur
        if ($sortie->getOrganisateur() !== $user) {
            throw $this->createAccessDeniedException("Vous n'êtes pas l'organisateur de cette sortie.");
        }

        $form = $this->createForm(AnnulationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//            // Vérif CSRF (optionnelle si pas déjà intégrée dans le formulaire)
//            $submittedToken = $request->request->get('_token');
//            if (!$this->isCsrfTokenValid('annuler' . $id, $submittedToken)) {
//                throw $this->createAccessDeniedException('Token CSRF invalide.');
//            }

            $motif = $form->get('motifAnnulation')->getData();

            // État "Annulée"
            $etatAnnulee = $em->getRepository(\App\Entity\Etat::class)->findOneBy(['libelle' => 'Annulée']);
            if (!$etatAnnulee) {
                throw new \Exception("L'état 'Annulée' est introuvable.");
            }

            $sortie->setEtat($etatAnnulee);
            $sortie->setMotifAnnulation($motif);
            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success', "Sortie annulée avec succès.");
            return $this->redirectToRoute('sortie_show', ['id' => $id]);
        }

        return $this->render('sorties/annuler.html.twig', [
            'sortie' => $sortie,
            'form' => $form->createView(),
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

        $user->addEstInscrit($sortie);
        $em->persist($sortie);
        $em->flush();

        $this->addFlash('success', 'Inscription réussie à la sortie !');
        return $this->redirectToRoute('sortie_show', ['id' => $id]);
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

        // Désinscription
        $sortie->removeUser($user);

        $user->removeEstInscrit($sortie);
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

