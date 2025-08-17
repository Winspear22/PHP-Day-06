<?php

namespace App\E03Bundle\Controller;

use Exception;
use App\Entity\Post;
use App\Form\PostType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class E03Controller extends AbstractController
{
    #[Route('/e03/read_post_details/{id}', name: 'e03_read_post_details', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function readPostDetails(int $id, ManagerRegistry $doctrine): Response
    {
        try
        {
            $post = $doctrine->getRepository(Post::class)->find($id);

            if (!$post)
            {
                $this->addFlash('error', 'Le post demandé est introuvable.');
                return $this->redirectToRoute('e03_read_all_posts');
            }

            return $this->render('e03/post_details.html.twig', [
                'post' => $post,
            ]);
        }
        catch (Exception $e)
        {
            $this->addFlash('error', 'Erreur, il y\'a eu un soucis dans l\'affichage du message : ' . $e->getMessage());
            return $this->redirectToRoute('e01_index');
        }
    }
    
    #[Route('/e03/create_post', name: 'e03_create_post')]
    #[IsGranted('ROLE_USER')]
    public function createPost(Request $request, ManagerRegistry $doctrine): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            if (!$form->isValid())
            {
                // On parcourt toutes les erreurs et on les envoie en flash
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
            else
            {
                try
                {
                    $post->setAuthor($this->getUser()); // auteur = user connecté
                    $post->setCreated(new \DateTimeImmutable());

                    $em = $doctrine->getManager();
                    $em->persist($post);
                    $em->flush();

                    $this->addFlash('success', 'Post créé avec succès !');
                    return $this->redirectToRoute('e03_read_all_posts');
                }
                catch (Exception $e)
                {
                    $this->addFlash('error', 'Erreur lors de la création du post : '.$e->getMessage());
                }
            }
        }
        return $this->render('e03/create_post.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /*#[Route('/e03/create_post', name: 'e03_create_post')]
    #[IsGranted('ROLE_USER')]
    public function createPost(Request $request, ManagerRegistry $doctrine): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
            {
            $post->setAuthor($this->getUser()); // auteur = utilisateur connecté
            $post->setCreated(new \DateTimeImmutable());

            $em = $doctrine->getManager();
            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post créé avec succès  !');
            return $this->redirectToRoute('e03_read_all_posts');
        }

        return $this->render('e03/create_post.html.twig', [
            'form' => $form->createView(),
        ]);
    }*/
}
