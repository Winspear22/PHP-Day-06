<?php

namespace App\E05Bundle\Controller;

use Exception;
use App\Entity\Post;
use App\Entity\Vote;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class E05Controller extends AbstractController
{
    #[Route(path: '/e05', name: 'e05_index')]
    public function index(): Response
    {
        $message = "popo";
        return $this->render('e01/index.html.twig', [
            'message' => $message
        ]);
    }

    #[Route('/e05/post/{id}/vote/{type}', name: 'e05_vote', requirements: ['type' => 'like|dislike'])]
    #[IsGranted('ROLE_USER')]
    public function vote(int $id, string $type, ManagerRegistry $doctrine, Request $request): Response
    {
        try {
            $post = $doctrine->getRepository(Post::class)->find($id);
            if (!$post) {
                $this->addFlash('error', 'Erreur, le post que vous cherchez n\'existe pas ou plus.');
                return $this->redirectToRoute('e01_welcome');
            }
            $user = $this->getUser();
            $userId = $user->getId();
            $postAuthorId = $post->getAuthor()->getId();
            $rep = $user->getReputation(); // Si jamais bug, fais appel à la méthode SQL (expliqué plus haut)

            if ($postAuthorId === $userId) {
                $this->addFlash('error', "Tu ne peux pas voter pour ton propre post.");
                return $this->redirectToRoute('e01_welcome');
            }
            if ($type === 'like' && $rep < 3) {
                $this->addFlash('error', "Il te faut au moins 3 de réputation pour liker.");
                return $this->redirectToRoute('e01_welcome');
            }
            if ($type === 'dislike' && $rep < 6) {
                $this->addFlash('error', "Il te faut au moins 6 de réputation pour disliker.");
                return $this->redirectToRoute('e01_welcome');
            }

            $em = $doctrine->getManager();
            $voteRepo = $doctrine->getRepository(Vote::class);
            $existing = $voteRepo->findOneBy(['user' => $user, 'post' => $post]);
            $isLikeRequested = ($type === 'like');
            if ($existing === null) {
                $newVote = new Vote();
                $newVote->setUser($user);
                $newVote->setPost($post);
                $newVote->setIsLike($isLikeRequested);
                $em->persist($newVote);
                $em->flush();
            } else if ($existing->isLike() === $isLikeRequested) {
                $em->remove($existing);
                $em->flush();
            } else {
                $existing->setIsLike($isLikeRequested);
                $em->flush();
            }
        } catch (UniqueConstraintViolationException $e) {
            $this->addFlash('error', 'Erreur : '.$e->getMessage());
            return $this->redirectToRoute('e01_welcome');
        } catch (Exception $e) {
            $this->addFlash('error', 'Erreur : '.$e->getMessage());
            return $this->redirectToRoute('e01_index');
        }
        $from = $request->query->get('from', 'welcome');
        if ($from === 'details')
            return $this->redirectToRoute('e03_read_post_details', ['id' => $id]);
        return $this->redirectToRoute('e01_welcome');
    }
}
