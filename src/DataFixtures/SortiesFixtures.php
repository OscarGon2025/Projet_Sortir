<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class SortiesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $site = (new Site())->setNom('Site X');
        $etat = (new Etat())->setLibelle('Ouverte');

        $lieu = (new Lieu())
            ->setNom('Place République')->setRue('janeiro')
            ->setVille('Rio')
            ->setCodePostal('35200')
            ->setLatitude(0)
            ->setLongitude(0);

        $organisateur = (new User())
            ->setNom('Dupont')
            ->setPrenom('Jean')->setTelephone('0600000000')
            ->setMail('j@x.fr')
            ->setAdministrateur(false)
            ->setActif(true)
            ->setEstRattacheA($site);

        $participant = (new User())
            ->setNom('Martin')
            ->setPrenom('Eva')
            ->setTelephone('0611111111')
            ->setMail('e@x.fr')
            ->setAdministrateur(false)
            ->setActif(true)
            ->setEstRattacheA($site);

        $sortie = (new Sortie())
            ->setNom('Sortie 1')
            ->setDateLimiteInscription(new \DateTime('now'))
            ->setDateHeureDebut(new \DateTimeImmutable('now'))
            ->setDuree(2)
            ->setNbInscriptionsMax(10)
            ->setInfoSortie('Sortie 1')
            ->setLieu($lieu)
            ->setSiteOrganisateur($site)
            ->setEtat($etat)
            ->setOrganisateur($organisateur);
        $manager->flush();

        $participant->addEstInscrit($sortie);

        $manager->persist($site);
        $manager->persist($etat);
        $manager->persist($lieu);
        $manager->persist($organisateur);
        $manager->persist($participant);
        $manager->persist($sortie);
        $manager->flush();

        //Faker data
        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 15; $i++) {
            $site = (new Site())->setNom($faker->city);
            $etat = (new Etat())->setLibelle('Ouverte');
            $lieu = (new Lieu())
                ->setNom($faker->streetName)
                ->setRue($faker->streetAddress)
                ->setVille($faker->city)
                ->setCodePostal($faker->postcode)
                ->setLatitude(0)
                ->setLongitude(0);

            $organisateur = (new User())
                ->setNom($faker->lastName)
                ->setPrenom($faker->firstName)
                ->setTelephone($faker->phoneNumber)
                ->setAdministrateur(false)
                ->setMail($faker->email)
                ->setActif(true)
                ->setEstRattacheA($site);

            $participant = (new User())
                ->setNom($faker->lastName)
                ->setPrenom($faker->firstName)
                ->setTelephone($faker->phoneNumber)
                ->setAdministrateur(false)
                ->setMail($faker->email)
                ->setActif(true)
                ->setEstRattacheA($site);

            $sortie = (new Sortie())
                ->setNom($faker->text(10))
                ->setDateLimiteInscription(new \DateTime('now'))
                ->setDateHeureDebut(new \DateTimeImmutable('now'))
                ->setDuree(2)
                ->setNbInscriptionsMax(10)
                ->setInfoSortie($faker->text(100))
                ->setLieu($lieu)
                ->setSiteOrganisateur($site)
                ->setEtat($etat)
                ->setOrganisateur($organisateur);

            $participant->addEstInscrit($sortie);

            $manager->persist($site);
            $manager->persist($etat);
            $manager->persist($lieu);
            $manager->persist($organisateur);
            $manager->persist($participant);
            $manager->persist($sortie);
        }

        $manager->flush();




    }

}
