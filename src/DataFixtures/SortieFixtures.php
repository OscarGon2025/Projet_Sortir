<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

//class SortiesFixtures extends Fixture
//{
//    public function load(ObjectManager $manager): void
//    {
//        // $product = new Product();
//        // $manager->persist($product);
//
//        $manager->flush();
//    }
//}

class SortieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $etat1 = new Etat();
        $etat1->setLibelle('Créée');
        $manager->persist($etat1);

        $etat2 = new Etat();
        $etat2->setLibelle('Ouverte');
        $manager->persist($etat2);


        $site = new Site();
        $site->setNom('Site principal');
        $manager->persist($site);


        $lieu = new Lieu();
        $lieu->setVille('Paris');
        $lieu->setCodePostal('A3234');
        $lieu->setNom('Lieu central');
        $lieu->setRue('123 Rue Exemple');
        $lieu->setLatitude(48.8566);
        $lieu->setLongitude(2.3522);
        $manager->persist($lieu);




        $sortie = new Sortie();
        $sortie->setNom('Sortie test');
        $sortie->setDateCreated(new \DateTimeImmutable('now'));
        $sortie->setDateHeureDebut(new \DateTimeImmutable('+7 days'));
        $sortie->setDateLimiteInscription(new \DateTime('+3 days'));
        $sortie->setDuree(120);
        $sortie->setNbInscriptionsMax(20);
        $sortie->setInfoSortie('Sortie créée via fixture complète');
        $sortie->setEtat($etat1);         // Asignar uno de los estados creados
        $sortie->setSiteOrganisateur($site);
        $sortie->setLieu($lieu);



        $manager->persist($sortie);

        $manager->flush();
    }
}
