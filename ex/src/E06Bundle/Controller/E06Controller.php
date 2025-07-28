<?php

namespace App\E06Bundle\Controller;

use Exception;
use Throwable;
use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Form\UserFormType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Exception as DoctrineDBALException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class E06Controller extends AbstractController
{
    #[Route('/e06', name: 'e06_index')]
    public function index(EntityManagerInterface $em): Response
    {
        try
		{
            return $this->render('index.html.twig');
        }
		catch (DoctrineDBALException $e)
		{
            $this->addFlash('error', 'La base de données est indisponible.');
            return $this->render('error_db.html.twig');
        }
		catch (Exception $e)
		{

    return $this->render('error_db_others.html.twig', [
    'error_message' => 'Erreur inattendue : ' . $e->getMessage(),
    'exception_message' => $e::class,
]);

        }
    }

    #[Route('/e06/need-auth', name: 'e06_need_auth')]
    public function needAuth(): Response
    {
        return $this->render('need_auth.html.twig');
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/e06/sign_out', name: 'e06_sign_out')]
    public function signOut(): void {}

    #[Route('/e06/sign_in', name: 'e06_sign_in')]
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

    #[Route('/e06/sign_up', name: 'e06_sign_up')]
    public function createUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);
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
                    $user->setRoles(['ROLE_USER']);
                    $em->persist($user);
                    $em->flush();

                    $this->addFlash('success', 'Inscription réussie ! Connecte-toi !');
                    return $this->redirectToRoute('e06_sign_in');
                }
            }
            catch (Throwable $e)
            {
                $this->addFlash('error', 'Erreur lors de la création de l\'utilisateur.');
                return $this->redirectToRoute('e06_index');
            }
        }

        return $this->render('sign_up.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/e06/welcome', name: 'e06_welcome')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted(attribute: 'ROLE_USER')]
    public function welcome(EntityManagerInterface $em): Response
    {
        try
        {
            $posts = $em->getRepository(Post::class)->findBy([], ['created' => 'DESC']);
            return $this->render('welcome.html.twig', [
                'user' => $this->getUser(),
                'posts' => $posts,
            ]);
        }
        catch (Throwable $e)
        {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'affichage de la page de bienvenue.');
            return $this->redirectToRoute('e06_index');
        }
    }

    #[Route('/e06/post/new', name: 'e06_post_new')]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function newPost(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            try 
            {
                $post->setAuthor($this->getUser());
                $post->setCreated(new \DateTimeImmutable());

                $em->persist($post);
                $em->flush();

                $this->addFlash('success', 'Post créé avec succès !');
                return $this->redirectToRoute('e06_welcome');
            }
            catch (Throwable $e)
            {
                $this->addFlash('error', 'Erreur lors de l\'enregistrement du post.');
                return $this->redirectToRoute('e06_post_new');
            }
        }

        return $this->render('post.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/e06/post/{id}', name: 'e06_post_show')]
    #[IsGranted('ROLE_USER')]
    public function showPost(Post $post): Response
    {
        try
        {
            return $this->render('post_show.html.twig', [
                'post' => $post,
            ]);
        } 
        catch (Throwable $e)
        {
            $this->addFlash('error', 'Erreur lors de l\'affichage du post.');
            return $this->redirectToRoute('e06_welcome');
        }
    }

    #[Route('/e06/post/{id}/edit', name: 'e06_post_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        // Empêcher un user de modifier un post qui ne lui appartient pas
        if ($post->getAuthor() !== $this->getUser())
        {
            throw $this->createAccessDeniedException("Tu ne peux modifier que tes propres posts.");
        }
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $post->setLastEditedAt(new \DateTimeImmutable());
            $post->setLastEditedBy($this->getUser());
            $em->flush();
            $this->addFlash('success', 'Post modifié avec succès !');
            return $this->redirectToRoute('e06_post_show', ['id' => $post->getId()]);
        }
        return $this->render('post_edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }
}