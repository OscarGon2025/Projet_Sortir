<?php



namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $site = new Site();
        $site->setNom('Rennes');
        $manager->persist($site);

        $this->addReference('site-rennes', $site);

        $manager->flush();
    }

}
