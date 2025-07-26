<?php

namespace App\E03Bundle\Controller;

use DateTime;
use Exception;
use Throwable;
use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Form\UserType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Exception as DoctrineDBALException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class E03Controller extends AbstractController
{
    #[Route('/e03', name: 'e03_index')]
    public function index(EntityManagerInterface $em): Response
    {
        try {
            $posts = $em->getRepository(Post::class)->findBy([], ['created' => 'DESC']);
            return $this->render('index.html.twig', [
                'user' => $this->getUser(),
                'posts' => $posts,
            ]);
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Erreur lors du chargement des posts.');
            return $this->redirectToRoute('e03_need_auth');
        }
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/e03/sign_out', name: 'e03_sign_out')]
    public function signOut(): void {}

    #[Route('/e03/need-auth', name: 'e03_need_auth')]
    public function needAuth(): Response
    {
        return $this->render('need_auth.html.twig');
    }

    #[Route('/e03/sign_in', name: 'e03_sign_in')]
    public function signIn(): Response
    {
        try {
            return $this->render('security/login.html.twig');
        } catch (DoctrineDBALException $e) {
            $this->addFlash('error', 'La base de données est indisponible.');
            return $this->render('error_db.html.twig');
        } catch (Exception $e) {
            $this->addFlash('error', 'Erreur inattendue : ' . $e->getMessage());
            return $this->render('error_db_others.html.twig');
        }
    }

    #[Route('/e03/sign_up', name: 'e03_sign_up')]
    public function createUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($em->getRepository(User::class)->findOneBy(['username' => $user->getUsername()])) {
                    $form->get('username')->addError(new FormError('Ce nom d\'utilisateur est déjà pris.'));
                } else {
                    $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
                    $user->setRoles(['ROLE_USER']);
                    $em->persist($user);
                    $em->flush();

                    $this->addFlash('success', 'Inscription réussie ! Connecte-toi !');
                    return $this->redirectToRoute('e03_sign_in');
                }
            } catch (\Throwable $e) {
                $this->addFlash('error', 'Erreur lors de la création de l\'utilisateur.');
                return $this->redirectToRoute('e03_index');
            }
        }

        return $this->render('sign_up.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/e03/welcome', name: 'e03_welcome')]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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
            return $this->redirectToRoute('e03_index');
        }
    }

    #[Route('/e03/post/new', name: 'e03_post_new')]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function newPost(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $post->setAuthor($this->getUser());
                $post->setCreated(new DateTime());

                $em->persist($post);
                $em->flush();

                $this->addFlash('success', 'Post créé avec succès !');
                return $this->redirectToRoute('e03_welcome');
            } catch (\Throwable $e) {
                $this->addFlash('error', 'Erreur lors de l\'enregistrement du post.');
                return $this->redirectToRoute('e03_post_new');
            }
        }

        return $this->render('post.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/e03/post/{id}', name: 'e03_post_show')]
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
            return $this->redirectToRoute('e03_welcome');
        }
    }
}
