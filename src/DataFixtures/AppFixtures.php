<?php

namespace App\DataFixtures;

use App\Entity\Conference;
use App\Entity\Organization;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $sensiolabs = $this->createOrganization('SensioLabs');
        $manager->persist($sensiolabs);

        $symfony = $this->createOrganization('Symfony SAS');
        $manager->persist($symfony);

        for ($i = 15; $i <= 25; $i++) {
            $conference = $this->createConference($i);
            $conference->addOrganization($sensiolabs);
            $conference->addOrganization($symfony);

            $conference->setCreatedBy($this->getUser('nobody'));

            $manager->persist($conference);
        }

        $conferenceWithoutOrganization = $this->createConference(26);
        $conferenceWithoutOrganization->setCreatedBy($this->getUser('admin'));

        $manager->persist($conferenceWithoutOrganization);

        $manager->flush();
    }

    private function createOrganization(string $name): Organization
    {
        $organization = new Organization();
        $organization->setName($name);
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

    private function getUser(string $username): User
    {
        return $this->getReference($username, User::class);
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
