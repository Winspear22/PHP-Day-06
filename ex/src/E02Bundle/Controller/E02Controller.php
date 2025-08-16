<?php

namespace App\E02Bundle\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
			return $this->redirectToRoute('e02_admin_panel');
		}

		return $this->render('e02/panel.html.twig', [
			'users' => $users,
		]);
	}


	/*#[Route('/e02/admin/users', name: 'e02_admin_users')]
	#[IsGranted('ROLE_ADMIN')]
	public function listUsers(EntityManagerInterface $em): Response
	{
		$users = $em->getRepository(User::class)->findAll();

		return $this->render('e02/users.html.twig', [
			'users' => $users,
		]);
}*/
}
