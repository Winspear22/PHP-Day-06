<?php

namespace App\E06Bundle\Controller;

use DateTime;
use Exception;
use App\Entity\Post;
use App\Form\PostType;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class E06Controller extends AbstractController
{
    #[Route(path: '/e06', name: 'e06_index')]
    public function index(): Response
    {
        $message = "popo";
        return $this->render('e01/index.html.twig', [
            'message' => $message
        ]);
    }

    #[Route('/e06/post/{id}/edit', name:'e06_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(int $id, ManagerRegistry $doctrine, Request $request): Response
    {
        try
        {
            $post = $doctrine->getRepository(Post::class)->find($id);
            if (!$post)
			{
				$this->addFlash('error', 'Erreur, le post que vous cherchez n\'existe pas ou plus.');
				return $this->redirectToRoute('e01_welcome');
			}
            $user = $this->getUser();
            $userId = $user->getId();
			$postAuthorId = $post->getAuthor()->getId();
			if ($postAuthorId !== $userId)
			{
				$this->addFlash('error', "Erreur, tu ne peux modifier uniquement tes propres posts.");
                return $this->redirectToRoute('e03_read_post_details', ['id' => $id]);
			}
            else
            {
                $form = $this->createForm(PostType::class, $post);
                $form->handleRequest($request);
                
                if ($form->isSubmitted() && $form->isValid())
                {
                    $post->setLastEditedBy($user);
                    $post->setLastEditedAt(new DateTimeImmutable());

                    $em = $doctrine->getManager();
                    $em->flush();
                    $this->addFlash('success', 'Post modifié avec succès.');
                    return $this->redirectToRoute('e03_read_post_details', ['id' => $id]);
                }
                return $this->render('e06/edit.html.twig', [
                    'post' => $post,
                    'form' => $form->createView(),
                ]);
            }
        }
        catch (Exception $e)
        {
            $message = "Erreur : " . $e->getMessage();
			$this->addFlash('error', $message);
			return $this->redirectToRoute('e01_welcome');
        }
		return $this->redirectToRoute('e03_read_post_details', ['id' => $id]);
    }
}
