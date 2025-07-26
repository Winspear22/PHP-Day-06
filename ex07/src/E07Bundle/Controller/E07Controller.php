<?php

namespace App\E07Bundle\Controller;

use Exception;
use Throwable;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Vote;
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
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class E07Controller extends AbstractController
{
    #[Route('/e07', name: 'e07_index')]
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

    #[Route('/e07/need-auth', name: 'e07_need_auth')]
    public function needAuth(): Response
    {
        return $this->render('need_auth.html.twig');
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/e07/sign_out', name: 'e07_sign_out')]
    public function signOut(): void {}

    #[Route('/e07/sign_in', name: 'e07_sign_in')]
    public function signIn(AuthenticationUtils $authenticationUtils): Response
    {
        try 
        {
            $error = $authenticationUtils->getLastAuthenticationError();
            $lastUsername = $authenticationUtils->getLastUsername();

            if ($error)
                $this->addFlash('danger', $error->getMessageKey());
            return $this->render('security/login.html.twig', [
                'last_username' => $lastUsername,
            ]);
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

    #[Route('/e07/sign_up', name: 'e07_sign_up')]
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
                {
                    $this->addFlash('error', 'Ce nom d\'utilisateur est déjà pris.');
                    return $this->redirectToRoute('e07_sign_up');
                }
                    //$form->get('username')->addError(new FormError('Ce nom d\'utilisateur est déjà pris.'));
                else
                {
                    $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
                    $user->setRoles(['ROLE_USER']);
                    $em->persist($user);
                    $em->flush();

                    $this->addFlash('success', 'Inscription réussie ! Connecte-toi !');
                    return $this->redirectToRoute('e07_sign_in');
                }
            }
            catch (Throwable $e)
            {
                $this->addFlash('error', 'Erreur lors de la création de l\'utilisateur.');
                return $this->redirectToRoute('e07_index');
            }
        }

        return $this->render('sign_up.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/e07/welcome', name: 'e07_welcome')]
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
            return $this->redirectToRoute('e07_index');
        }
    }

    #[Route('/e07/post/new', name: 'e07_post_new')]
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

                $post->setLastEditedAt(new \DateTimeImmutable());
                $post->setLastEditedBy($this->getUser());

                $em->persist($post);
                $em->flush();

                $this->addFlash('success', 'Post créé avec succès !');
                return $this->redirectToRoute('e07_welcome');
            }
            catch (Throwable $e)
            {
                $this->addFlash('error', 'Erreur lors de l\'enregistrement du post.');
                return $this->redirectToRoute('e07_post_new');
            }
        }

        return $this->render('post.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/e07/post/{id}', name: 'e07_post_show')]
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
            return $this->redirectToRoute('e07_welcome');
        }
    }

    #[Route('/e07/post/{id}/vote/{type}', name: 'e07_post_vote', requirements: ['type' => 'like|dislike'])]
    #[IsGranted('ROLE_USER')]
    public function vote(Post $post, string $type, EntityManagerInterface $em): Response
    {
        if (!in_array($type, ['like', 'dislike'])) {
            throw $this->createNotFoundException('Type de vote invalide.');
        }

        /** @var User $voter */
        $voter = $this->getUser();
        /** @var User $author */
        $author = $post->getAuthor();

        if ($author === $voter)
        {
            $this->addFlash('error', 'Tu ne peux pas voter pour ton propre post.');
            return $this->redirectToRoute('e07_welcome');
        }

        $existingVote = $em->getRepository(Vote::class)->findOneBy([
            'user' => $voter,
            'post' => $post,
        ]);

        $reputation = $voter->getReputation();
        if ($type === 'like' && $reputation < 3 && !$voter->isAdmin())
        {
            $this->addFlash('error', 'Tu dois avoir au moins 3 points de réputation pour liker.');
            return $this->redirectToRoute('e07_welcome');
        }
        if ($type === 'dislike' && $reputation < 6 && !$voter->isAdmin())
        {
            $this->addFlash('error', 'Tu dois avoir au moins 6 points de réputation pour disliker.');
            return $this->redirectToRoute('e07_welcome');
        }

        $isLike = $type === 'like';

        if ($existingVote)
        {
            if ($existingVote->getIsLike() === $isLike)
            {
                // Même vote → on retire et on met à jour la réputation
                if ($isLike)
                    $author->decreaseReputation(1);
                else
                    $author->increaseReputation(1);
                $em->remove($existingVote);
                $this->addFlash('info', 'Ton vote a été retiré.');
            }
            else
            {
                // Vote opposé → on met à jour le vote et la réputation
                $existingVote->setIsLike($isLike);
                if ($isLike)
                {
                    $author->increaseReputation(1);
                    $author->decreaseReputation(1); // annule le dislike précédent
                }
                else
                {
                    $author->decreaseReputation(1);
                    $author->increaseReputation(1); // annule le like précédent
                }
                $this->addFlash('success', 'Ton vote a été mis à jour.');
            }
        }
        else
        {
            // Aucun vote existant → on crée
            $vote = new Vote();
            $vote->setUser($voter);
            $vote->setPost($post);
            $vote->setIsLike($isLike);

            if ($isLike) {
                $author->increaseReputation(1);
            } else {
                $author->decreaseReputation(1);
            }

            $em->persist($vote);
            $this->addFlash('success', 'Ton vote a été enregistré.');
        }

        $em->flush();

        return $this->redirectToRoute('e07_welcome');
    }

    #[Route('/e07/post/{id}/edit', name: 'e07_post_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Règles d'édition
        $isOwner = $post->getAuthor() === $user;
        $hasReputation = $user->getReputation() >= 9;
        $isAdmin = $user->isAdmin();

        $isAuthorAdmin = $post->getAuthor()?->isAdmin();

        // Bloque si l’auteur du post est admin et que l’utilisateur courant ne l’est pas
        if ($isAuthorAdmin && !$isAdmin)
        {
            $this->addFlash('error', 'Seuls les administrateurs peuvent éditer les posts d\'autres administrateurs.');
            return $this->redirectToRoute('e07_welcome');
        }

        if (!$isOwner && !$hasReputation && !$isAdmin)
        {
            // Flash uniquement si c'est une restriction de réputation
            $this->addFlash('error', 'Vous n\'avez plus les 9 points de réputation requis pour éditer ce post.');
            return $this->redirectToRoute('e07_welcome');
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $post->setLastEditedAt(new \DateTimeImmutable());
            $post->setLastEditedBy($user);
            $em->flush();

            $this->addFlash('success', 'Post modifié avec succès !');
            return $this->redirectToRoute('e07_post_show', ['id' => $post->getId()]);
        }

        return $this->render('post_edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
        }




        
}
