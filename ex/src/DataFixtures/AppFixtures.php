<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Vote;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // Comptes de base
        $u0 = $this->makeUser($manager, 'u0',  ['ROLE_USER'], 'u0');
        $u3 = $this->makeUser($manager, 'u3',  ['ROLE_USER'], 'u3');
        $u6 = $this->makeUser($manager, 'u6',  ['ROLE_USER'], 'u6');
        $u9 = $this->makeUser($manager, 'u9',  ['ROLE_USER'], 'u9');
        $admin = $this->makeUser($manager, 'admin42', ['ROLE_ADMIN'], 'admin42');

        // Votants génériques
        $voters = [];
        for ($i = 1; $i <= 12; $i++) {
            $voters[] = $this->makeUser($manager, "voter$i", ['ROLE_USER'], 'voter');
        }

        // Posts
        $p0 = $this->makePost($manager, $u0, 'Post u0', 'Contenu u0');
        $p3 = $this->makePost($manager, $u3, 'Post u3', 'Contenu u3');
        $p6 = $this->makePost($manager, $u6, 'Post u6', 'Contenu u6');
        $p9 = $this->makePost($manager, $u9, 'Post u9', 'Contenu u9');

        $manager->flush(); // pour générer les IDs avant de voter

        // Votes (pour générer précisément les réputations qu'on veut)
        // u3 reçoit 3 likes
        $this->like($manager, $voters[0], $p3);
        $this->like($manager, $voters[1], $p3);
        $this->like($manager, $voters[2], $p3);

        // u6 reçoit 6 likes
        for ($i = 3; $i < 9; $i++) {
            $this->like($manager, $voters[$i], $p6);
        }

        // u9 reçoit exactement 9 de réputation nette : 10 likes – 1 dislike
        for ($i = 0; $i < 10; $i++) {
            $this->like($manager, $voters[$i], $p9);
        }
        $this->dislike($manager, $voters[10], $p9);

        $manager->flush();
    }

    private function makeUser(ObjectManager $em, string $username, array $roles, string $plain): User
    {
        $u = new User();
        $u->setUsername($username);
        $u->setRoles($roles);
        $u->setPassword($this->hasher->hashPassword($u, $plain));
        $em->persist($u);
        return $u;
    }

    private function makePost(ObjectManager $em, User $author, string $title, string $content): Post
    {
        $p = new Post();
        $p->setAuthor($author);
        $p->setTitle($title);
        $p->setContent($content);
        $p->setCreated(new \DateTimeImmutable());
        $em->persist($p);
        return $p;
    }

    private function like(ObjectManager $em, User $by, Post $post): void
    {
        if ($post->getAuthor()->getId() === $by->getId()) {
            return; // pas de vote sur soi-même
        }
        $v = new Vote();
        $v->setUser($by);
        $v->setPost($post);
        $v->setIsLike(true);
        $em->persist($v);
    }

    private function dislike(ObjectManager $em, User $by, Post $post): void
    {
        if ($post->getAuthor()->getId() === $by->getId()) {
            return;
        }
        $v = new Vote();
        $v->setUser($by);
        $v->setPost($post);
        $v->setIsLike(false);
        $em->persist($v);
    }
}
