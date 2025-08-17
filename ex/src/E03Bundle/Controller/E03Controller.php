<?php

namespace App\E03Bundle\Controller;

use App\Entity\Post;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class E03Controller extends AbstractController
{
    #[Route('/e03/read_post_details/{id}', name: 'e03_read_post_details', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function readPostDetails(): Response
    {
        return $this->render('e03/post_details.html.twig');
    }

    #[Route('/e03/read_all_posts', name:  'e03_read_all_posts')]
    #[IsGranted('ROLE_USER')]
    public function readAllPosts(ManagerRegistry $doctrine): Response
    {
        $posts = $doctrine->getRepository(Post::class)->findBy([], ['created' => 'DESC']);
        return $this->render('e03/index.html.twig', [
            'posts' => $posts,
        ]);
    }
}
