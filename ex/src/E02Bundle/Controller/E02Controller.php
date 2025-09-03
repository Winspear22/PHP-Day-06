<?php

namespace App\E02Bundle\Controller;

use Exception;
use Throwable;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class E02Controller extends AbstractController
{
	#[Route('/e02', name: 'ex02_index')]
	public function index(): Response
	{
		$message = "Bienvenue sur la page E02.";
		return $this->render('e01/index.html.twig', [
			'message' => $message
		]);
	}

	#[Route('/e02/admin', name: 'e02_admin_panel')]
	#[IsGranted('ROLE_ADMIN')]
	public function panel(EntityManagerInterface $em): Response
	{
		try
		{
			$users = $em->getRepository(User::class)->findAll();
		}
		catch (Exception $e)
		{
			$message = "Error, we could not get the users list : " . $e->getMessage();
			$this->addFlash('danger', $message);
			return $this->redirectToRoute('e01_index');
		}

		return $this->render('e02/panel.html.twig', [
			'users' => $users,
		]);
	}

	#[Route('/e02/admin/user/{id}/delete', name: 'e02_admin_user_delete', methods: ['POST'])]
	#[IsGranted('ROLE_ADMIN')]
	public function deleteUser(User $user, Request $request, ManagerRegistry $doctrine): Response
	{
		// 1) CSRF
		if (!$this->isCsrfTokenValid('delete_user_'.$user->getId(), $request->request->get('_token')))
		{
			$this->addFlash('error', 'Token CSRF invalide.');
			return $this->redirectToRoute('e02_admin_panel');
		}

		// 2) Interdiction de se supprimer soi-même
		$current = $this->getUser();
		if ($current instanceof User && $current->getId() === $user->getId())
		{
			$this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
			return $this->redirectToRoute('e02_admin_panel');
		}

		// 3) Interdiction de supprimer un autre admin
		if (in_array('ROLE_ADMIN', $user->getRoles(), true))
		{
			$this->addFlash('error', 'Vous ne pouvez pas supprimer un autre administrateur.');
			return $this->redirectToRoute('e02_admin_panel');
		}

		// 4) Suppression
		$em = $doctrine->getManager();
		try
		{
			$em->remove($user);
			$em->flush();
			$this->addFlash('success', "L'utilisateur {$user->getUsername()} a été supprimé.");
		}
		catch (Throwable $e)
		{
			$this->addFlash('error', 'Erreur lors de la suppression : '.$e->getMessage());
		}

		return $this->redirectToRoute('e02_admin_panel');
	}
}
