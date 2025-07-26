<?php

namespace App\E02Bundle\Controller;

use Exception;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\DBAL\Exception as DoctrineDBALException;

class E02Controller extends AbstractController
{
    #[Route('/e02', name: 'e02_index')]
    public function index(): Response
    {
        return $this->render('index.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/e02/create_user', name: 'e02_create_user', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_ADMIN')]
    public function createManyUsers(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        try 
		{
			$user0 = $em->getRepository(User::class)->findOneBy(['username' => 'user0']);

			if ($user0 !== null)
			{
				$this->addFlash('error', 'Les utilisateurs sont déjà créés.');
				return $this->redirectToRoute('e02_admin');
			}
            $currentUser = $this->getUser();
			foreach ($em->getRepository(User::class)->findAll() as $user)
			{
				if ($user !== $currentUser && !in_array('ROLE_ADMIN', $user->getRoles(), true))
					$em->remove($user);
			}
            $em->flush();

            for ($i = 0; $i < 10; $i++)
			{
                $user = new User();
                $user->setUsername('user' . $i);
                $user->setPassword($passwordHasher->hashPassword($user, '123'));
                $user->setRoles(['ROLE_USER']);
                $em->persist($user);
            }
            $em->flush();

            $this->addFlash('success', '10 users créés avec succès.');
        }
		catch (Exception $e)
		{
            $this->addFlash('error', 'Erreur lors de la création des users : ' . $e->getMessage());
        }

        return $this->redirectToRoute('e02_admin');
    }

    #[Route('/e02/sign_up', name: 'e02_sign_up')]
    public function createAdmin(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
		{
            try
			{
                if ($em->getRepository(User::class)->findOneBy(['username' => $user->getUsername()]))
                    $form->get('username')->addError(new FormError('Ce nom d\'utilisateur est déjà pris.'));
				else
				{
                    $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
                    $user->setRoles(['ROLE_ADMIN']);
                    $em->persist($user);
                    $em->flush();
                    $this->addFlash('success', 'Inscription réussie ! Connecte-toi !');
                    return $this->redirectToRoute('e02_sign_in');
                }
            }
			catch (Exception $e)
			{
                $this->addFlash('error', 'Erreur lors de la création de l\'admin : ' . $e->getMessage());
                return $this->redirectToRoute('e02_index');
            }
        }

        return $this->render('sign_up.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/e02/sign_in', name: 'e02_sign_in')]
    public function signIn(): Response
    {
        try
		{
            return $this->render('security/login.html.twig');
        }
		catch (DoctrineDBALException $e)
		{
            $this->addFlash('error', 'La base de données est indisponible.');
            return $this->render('error_db.html.twig');
        }
		catch (Exception $e)
		{
            $this->addFlash('error', 'Erreur inattendue : ' . $e->getMessage());
            return $this->render('error_db_others.html.twig');
        }
    }

    #[Route('/e02/admin', name: 'e02_admin')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_ADMIN')]
    public function admin(UserRepository $userRepository): Response
    {
        try
		{
            $users = $userRepository->findAll();
        }
		catch (Exception $e)
		{
            $this->addFlash('error', 'Erreur lors de l\'affichage des utilisateurs : ' . $e->getMessage());
            $users = [];
        }

        return $this->render('admin.html.twig', ['users' => $users]);
    }

    #[Route('/e02/welcome', name: 'e02_welcome')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function welcome(): Response
    {
        try
		{
            return $this->render('welcome.html.twig', [
                'user' => $this->getUser(),
            ]);
        }
		catch (Exception $e)
		{
            $this->addFlash('error', 'Une erreur est survenue lors de l\'affichage de la page de bienvenue.');
            return $this->redirectToRoute('e02_index');
        }
    }

    #[Route('/e02/sign_out', name: 'e02_sign_out')]
    public function signOut(): void {}

    #[Route('/e02/need-auth', name: 'e02_need_auth')]
    public function needAuth(): Response
    {
        return $this->render('need_auth.html.twig');
    }

    #[Route('/e02/admin/delete/{id}', name: 'e02_admin_delete', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(int $id, UserRepository $userRepo, EntityManagerInterface $em): Response
    {
        $user = $userRepo->find($id);

        if (!$user)
            $this->addFlash('error', "L'utilisateur demandé n'existe pas.");
        else if ($user === $this->getUser())
            $this->addFlash('error', 'Tu ne peux pas te supprimer toi-même.');
        else if (in_array('ROLE_ADMIN', $user->getRoles(), true))
            $this->addFlash('error', 'Tu ne peux pas supprimer un autre administrateur.');
        else
		{
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }
        return $this->redirectToRoute('e02_admin');
    }
}
