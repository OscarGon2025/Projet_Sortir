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


        $site = $this->getReference('site-rennes', Site::class);


        if (!$site) {
            throw new \Exception('Site "Rennes" not found');
        }

        // --- USER 1 ---
        $user1 = new User();
        $user1->setEstRattacheA($site);
        $user1->setUsername('Batman');
        $user1->setNom('Wayne');
        $user1->setPrenom('Bruce');
        $user1->setTelephone('0123456789');
        $user1->setPassword($this->passwordHasher->hashPassword($user1, '123456'));
        $user1->setMail('batman@example.com');
        $user1->setAdministrateur(true);
        $user1->setActif(true);
        $manager->persist($user1);
        $this->setReference('user1', $user1);

        // --- 10 USERS ---
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEstRattacheA($site);
            $user->setUsername("user$i");
            $user->setNom("Nom$i");
            $user->setPrenom("Prenom$i");
            $user->setTelephone('0123456789');
            $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));
            $user->setMail("user$i@example.com");
            $user->setAdministrateur(false);
            $user->setActif(true);
            $manager->persist($user);

            // Opcional: si quieres usar estos también como referencia
            $this->setReference("user$i", $user);
        }

        // --- USER 2 ---
        $user2 = new User();
        $user2->setUsername("BWayne");
        $user2->setNom('Wayne');
        $user2->setPrenom('Bruce');
        $user2->setMail("bwayne@example.com");
        $user2->setEstRattacheA($site);
        $user2->setAdministrateur(false);
        $user2->setActif(true);
        $user2->setTelephone('0123456789');
        $user2->setPassword($this->passwordHasher->hashPassword($user2, "123456"));
        $manager->persist($user2);
        $this->setReference('user2', $user2);

        $manager->flush();
    }
}
