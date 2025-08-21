<?php

namespace App\Service;

use App\Entity\Site;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserImportService
{
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->em = $em;
    }

    public function importUsers(string $filePath): int
    {

        if (!file_exists($filePath)) {
            throw new \Exception("Le fichier $filePath n'existe pas.");
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \Exception("Le fichier $filePath n'a pas pu être ouvert.");
        }
        $count = 0;

        fgetcsv($handle, 1000, ",");

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            // ignorer les lignes vides
            if (count($data) < 9) {
                continue;
            }

            // forcer à avoir exactement 9 colonnes (si ; en trop → coupées)
            $data = array_slice(array_pad($data, 9, null), 0, 9);

            [
                $username,
                $nom,
                $prenom,
                $telephone,
                $password,
                $mail,
                $administrateur,
                $actif,
                $estRattacheA,
            ] = $data;

            $user = new User();
            $user->setUsername($username);
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setTelephone($telephone);

            // ⚠️ ici tu stockes le mot de passe en clair → on hash :
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $user->setMail($mail);
            $user->setAdministrateur((bool)$administrateur);
            $user->setActif((bool)$actif);

            $site = $this->em->getRepository(Site::class)->find($estRattacheA);
            if (!$site) {
                throw new \Exception("Site avec l'ID $estRattacheA non trouvé");
            }
            $user->setEstRattacheA($site);


            $this->em->persist($user);

            $count++;
        }


        fclose($handle);
        $this->em->flush();
        return $count;

    }


}