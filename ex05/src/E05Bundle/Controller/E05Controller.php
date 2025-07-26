<?php

namespace App\E05Bundle\Controller;

use Exception;
use Throwable;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Vote;
use App\Form\PostType;
use DateTimeImmutable;
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

class E05Controller extends AbstractController
{
    #[Route('/e05', name: 'e05_index')]
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

    #[Route('/e05/need-auth', name: 'e05_need_auth')]
    public function needAuth(): Response
    {
        return $this->render('need_auth.html.twig');
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/e05/sign_out', name: 'e05_sign_out')]
    public function signOut(): void {}

    #[Route('/e05/sign_in', name: 'e05_sign_in')]
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

    #[Route('/e05/sign_up', name: 'e05_sign_up')]
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
                    return $this->redirectToRoute('e05_sign_in');
                }
            }
            catch (Throwable $e)
            {
                $this->addFlash('error', 'Erreur lors de la création de l\'utilisateur.');
                return $this->redirectToRoute('e05_index');
            }
        }

        return $this->render('sign_up.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/e05/welcome', name: 'e05_welcome')]
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
            return $this->redirectToRoute('e05_index');
        }
    }

    #[Route('/e05/post/new', name: 'e05_post_new')]
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
                return $this->redirectToRoute('e05_welcome');
            }
            catch (Throwable $e)
            {
                $this->addFlash('error', 'Erreur lors de l\'enregistrement du post.');
                return $this->redirectToRoute('e05_post_new');
            }
        }

        return $this->render('post.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/e05/post/{id}', name: 'e05_post_show')]
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
            return $this->redirectToRoute('e05_welcome');
        }
    }

    #[Route('/e05/post/{id}/vote/{type}', name: 'e05_post_vote', requirements: ['type' => 'like|dislike'])]
    #[IsGranted('ROLE_USER')]
    public function vote(Post $post, string $type, EntityManagerInterface $em): Response
    {
        if (!in_array($type, ['like', 'dislike'])) 
        {
            throw $this->createNotFoundException('Type de vote invalide.');
        }
        $user = $this->getUser();

        if ($post->getAuthor() === $user)
        {
            $this->addFlash('error', 'Tu ne peux pas voter pour ton propre post.');
            return $this->redirectToRoute('e05_welcome');
        }

        $existingVote = $em->getRepository(Vote::class)->findOneBy([
            'user' => $user,
            'post' => $post,
        ]);

        $isLike = $type === 'like';

        if ($existingVote)
        {
            // Même vote qu’avant → on annule
            if ($existingVote->getIsLike() === $isLike)
            {
                $em->remove($existingVote);
                $em->flush();

                $this->addFlash('info', 'Ton vote a été retiré.');
            }
            else
            {
                // Vote opposé → on change
                $existingVote->setIsLike($isLike);
                $em->flush();

                $this->addFlash('success', 'Ton vote a été mis à jour.');
            }
        } 
        else
        {
            // Aucun vote existant → on crée
            $vote = new Vote();
            $vote->setUser($user);
            $vote->setPost($post);
            $vote->setIsLike($isLike);

            $em->persist($vote);
            $em->flush();

            $this->addFlash('success', 'Ton vote a été enregistré.');
        }
        return $this->redirectToRoute('e05_welcome');
    }


}
