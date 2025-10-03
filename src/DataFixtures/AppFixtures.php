<?php

namespace App\DataFixtures;

use App\Entity\Conference;
use App\Entity\Organization;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $organization = $this->createOrganization();
        $manager->persist($organization);

        for ($i = 15; $i <= 25; $i++) {
            $conference = $this->createConference($i);
            $conference->addOrganization($organization);

            $manager->persist($conference);
        }

        $manager->flush();
    }

    private function createOrganization(): Organization
    {
        $organization = new Organization();
        $organization->setName('SensioLabs');
        $organization->setPresentation('Creator of Symfony !');
        $organization->setCreatedAt(new DateTimeImmutable('2010-05-12'));

        return $organization;
    }

    private function createConference(int $year): Conference
    {
        $year = '20'.str_pad($year, 2, '0', STR_PAD_LEFT);

        $conference = new Conference();
        $conference->setName('SymfonyCon ' . $year);
        $conference->setDescription('some description for symfony con.');
        $conference->setStartAt(new DateTimeImmutable("{$year}-05-12"));
        $conference->setEndAt(new DateTimeImmutable("{$year}-05-15"));
        $conference->setAccessible(true);

        return $conference;
    }
}
