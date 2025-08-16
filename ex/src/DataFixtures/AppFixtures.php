<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Utilisateur normal
        $user = new User();
        $user->setUsername('user42');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->hasher->hashPassword($user, 'user42'));
        $manager->persist($user);

        for ($i = 1; $i <= 10; $i++)
        {
            $u = new User();
            $u->setUsername("user$i");
            $u->setRoles(['ROLE_USER']);
            $u->setPassword($this->hasher->hashPassword($u, 'user42'));
            $manager->persist($u);
        }

        // Administrateur
        $admin = new User();
        $admin->setUsername('admin42');
        $admin->setRoles(['ROLE_ADMIN']); // clé ici !
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin42'));
        $manager->persist($admin);

        $admin = new User();
        $admin->setUsername('admin21');
        $admin->setRoles(['ROLE_ADMIN']); // clé ici !
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin21'));
        $manager->persist($admin);



        $manager->flush();
    }
}
