<?php

namespace App\DataFixtures;

use App\Entity\Site;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher){
        $this->passwordHasher = $passwordHasher;
    }


    public function getDependencies(): array
    {
        return [
            SiteFixtures::class,
        ];
    }


    public function load(ObjectManager $manager): void
    {
        $site = $manager->getRepository(Site::class)->findOneBy(['nom' => 'Rennes']);

        if (!$site) {
            throw new \Exception('Site Rennes not found');
        }

        $user = new User();
        $user->setEstRattacheA($site);
        $user->setUsername('Batman');
        $user->setNom('Wayne');
        $user->setPrenom('Bruce');
        $user->setTelephone('0123456789');;
        $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));
        $user->setMail('batman@example.com');
        $user->setAdministrateur(true);
        $user->setActif(true);

        $manager->persist($user);

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEstRattacheA($site);
            $user->setUsername("user$i");
            $user->setNom("Nom$i");
            $user->setPrenom("Prenom$i");
            $user->setTelephone('0123456789');;
            $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));
            $user->setMail("user$i@example.com");
            $user->setAdministrateur(false);
            $user->setActif(true);

            $manager->persist($user);
        }

        $manager->flush();

        // USER PAR DEFAUT
        $user = new User();
        $user->setUsername("BWayne");
        $user->setNom('Wayne');
        $user->setPrenom('Bruce');
        $user->setMail("bwayne@example.com");
        $user->setEstRattacheA($site);
        $user->setAdministrateur(false);
        $user->setActif(true);
        $user->setTelephone('0123456789');;

        // générer un mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, "123456");

        // setter le mot de passe généré
//        $user->setPassword($hashedPassword);

        $manager->persist($user);
        $manager->flush();



    }
}
