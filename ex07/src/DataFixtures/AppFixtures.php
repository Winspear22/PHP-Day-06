<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Vote;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $users = [];

        // Utilisateur 0 points (aucun post, aucun vote)
        $newbie = new User();
        $newbie->setUsername('newbie');
        $newbie->setRoles(['ROLE_USER']);
        $newbie->setPassword($this->hasher->hashPassword($newbie, 'pass'));
        $newbie->setReputation(0);
        $manager->persist($newbie);
        $users[] = $newbie;

        // Utilisateur 3 points (1 post avec 3 likes)
        $liker = new User();
        $liker->setUsername('liker');
        $liker->setRoles(['ROLE_USER']);
        $liker->setPassword($this->hasher->hashPassword($liker, 'pass'));
        $manager->persist($liker);
        $users[] = $liker;

        // Utilisateur 6 points (1 post avec 6 likes)
        $disliker = new User();
        $disliker->setUsername('disliker');
        $disliker->setRoles(['ROLE_USER']);
        $disliker->setPassword($this->hasher->hashPassword($disliker, 'pass'));
        $manager->persist($disliker);
        $users[] = $disliker;

        // Utilisateur 9 points (1 post avec 9 likes)
        $editor = new User();
        $editor->setUsername('editor');
        $editor->setRoles(['ROLE_USER']);
        $editor->setPassword($this->hasher->hashPassword($editor, 'pass'));
        $manager->persist($editor);
        $users[] = $editor;

        // Utilisateur admin
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin'));
        $admin->setReputation(999);
        $manager->persist($admin);

        // Création de 9 likers supplémentaires (reputation = 3, pas de posts)
        $extraLikers = [];
        for ($i = 1; $i <= 9; $i++) {
            $user = new User();
            $user->setUsername("liker_$i");
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->hasher->hashPassword($user, 'pass'));
            $user->setReputation(3);
            $manager->persist($user);
            $extraLikers[] = $user;
        }

        // Post de liker → 3 likes
        $post1 = new Post();
        $post1->setTitle('Post de liker');
        $post1->setContent('Contenu du post de liker');
        $post1->setCreated(new \DateTimeImmutable('-10 days'));
        $post1->setAuthor($liker);
        $post1->setLastEditedAt($post1->getCreated());
        $post1->setLastEditedBy($liker);
        $manager->persist($post1);

        for ($i = 0; $i < 3; $i++) {
            $vote = new Vote();
            $vote->setPost($post1);
            $vote->setUser($extraLikers[$i]);
            $vote->setIsLike(true);
            $manager->persist($vote);
        }
        $liker->setReputation(3);

        // Post de disliker → 6 likes
        $post2 = new Post();
        $post2->setTitle('Post de disliker');
        $post2->setContent('Contenu du post de disliker');
        $post2->setCreated(new \DateTimeImmutable('-7 days'));
        $post2->setAuthor($disliker);
        $post2->setLastEditedAt($post2->getCreated());
        $post2->setLastEditedBy($disliker);
        $manager->persist($post2);

        for ($i = 3; $i < 9; $i++) {
            $vote = new Vote();
            $vote->setPost($post2);
            $vote->setUser($extraLikers[$i]);
            $vote->setIsLike(true);
            $manager->persist($vote);
        }
        $disliker->setReputation(6);

        // Post de editor → 9 likes
        $post3 = new Post();
        $post3->setTitle('Post de editor');
        $post3->setContent('Contenu du post de editor');
        $post3->setCreated(new \DateTimeImmutable('-4 days'));
        $post3->setAuthor($editor);
        $post3->setLastEditedAt($post3->getCreated());
        $post3->setLastEditedBy($editor);
        $manager->persist($post3);

        foreach ($extraLikers as $voter) {
            $vote = new Vote();
            $vote->setPost($post3);
            $vote->setUser($voter);
            $vote->setIsLike(true);
            $manager->persist($vote);
        }
        $editor->setReputation(9);

        $manager->flush();
    }
}
