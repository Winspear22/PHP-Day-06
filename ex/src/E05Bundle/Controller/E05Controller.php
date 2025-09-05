<?php

namespace App\E05Bundle\Controller;

use Exception;
use App\Entity\Post;
use App\Entity\Vote;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class E05Controller extends AbstractController
{
    #[Route('/e05', name: 'e05_index')]
    public function index(): Response
    {
        $message = "popo";
        return $this->render('e01/index.html.twig', [
            'message' => $message
        ]);
    }

    #[Route('/e05/post/{id}/vote/{type}', name: 'e05_vote', requirements: ['type' => 'like|dislike'])]
    #[IsGranted('ROLE_USER')]
    public function vote(int $id, string $type, ManagerRegistry $doctrine): Response
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
			if ($postAuthorId === $userId)
			{
				$this->addFlash('error', "Tu ne peux pas voter pour ton propre post.");
				return $this->redirectToRoute('e01_welcome');
			}
			$em = $doctrine->getManager();
			$voteRepo = $doctrine->getRepository(Vote::class);
			$existing = $voteRepo->findOneBy(['user' => $user, 'post' => $post]);
			$isLikeRequested = ($type === 'like'); // Ca renvoit true ou false, c'est un if deguise en fait
			if ($existing === null)
			{
				$newVote = new Vote();
				$newVote->setUser($user);
				$newVote->setPost($post);
				$newVote->setIsLike($isLikeRequested);
				$em->persist($newVote);
				$em->flush();
				return $this->redirectToRoute('e03_read_post_details', ['id' => $post->getId()]);
			}
			else if ($existing->isLike() === $isLikeRequested)
			{
				$em->remove($existing);
				$em->flush();
				return $this->redirectToRoute('e03_read_post_details', ['id' => $post->getId()]);
			}
			else
			{
				$existing->setIsLike($isLikeRequested);
				$em->flush();
				return $this->redirectToRoute('e03_read_post_details', ['id' => $post->getId()]);
			}
		}
		catch (UniqueConstraintViolationException $e)
		{
    		// déjà un vote en DB → on fait comme le Cas B (annulation) ou on renvoie juste vers détails
			$message = "Erreur : " . $e->getMessage();
			$this->addFlash('error', $message);
			return $this->redirectToRoute('e01_welcome');
		}
		catch (Exception $e)
		{
			$message = "Erreur : " . $e->getMessage();
			$this->addFlash('error', $message);
			return $this->redirectToRoute('e01_index');
		}
		return $this->redirectToRoute('e01_welcome');
    }
}
