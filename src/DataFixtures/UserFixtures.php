<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final class UserFixtures extends Fixture
{
    private const USERS = [
        [
            'username' => 'nobody',
            'password' => 'nobody',
            'roles' => [],
        ],
        [
            'username' => 'user',
            'password' => 'user',
            'roles' => ['ROLE_USER'],
        ],
        [
            'username' => 'website',
            'password' => 'website',
            'roles' => ['ROLE_WEBSITE'],
        ],
        [
            'username' => 'organizer',
            'password' => 'organizer',
            'roles' => ['ROLE_ORGANIZER'],
        ],
        [
            'username' => 'admin',
            'password' => 'admin',
            'roles' => ['ROLE_ADMIN'],
        ],
    ];

    public function __construct(
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::USERS as $userData) {
            [
                'username' => $username,
                'password' => $password,
                'roles' => $roles,
            ] = $userData;

            $user = (new User())
                ->setUsername($username)
                ->setPassword($this->passwordHasherFactory->getPasswordHasher(User::class)->hash($password))
                ->setRoles($roles)
            ;

            $manager->persist($user);
        }

        $manager->flush();
    }
}
