<?php

namespace App\E04Bundle\Controller;

use Exception;
use Throwable;
use App\Entity\User;
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

class E04Controller extends AbstractController
{
    #[Route('/e04', name: 'e04_index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $animals = ['dog', 'cat', 'tiger', 'fox', 'owl', 'koala', 'panda', 'eagle', 'zebra', 'wolf'];
        $now = time();

        try
        {
            // Protection BDD
            //$em->getRepository(User::class)->count();

            // Gestion de session anonyme
            //dump($request->getSession()->all());

            if (!$session->has('anon_name') || !$session->has('last_access')
            || $now - $session->get('last_access') > 60)
            {
                $name = 'Anonymous ' . $animals[array_rand($animals)];
                $session->set('anon_name', $name);
                $session->set('last_access', $now);
                $elapsed = null;
            }
            else
            {
                $name = $session->get('anon_name');
                $elapsed = $now - $session->get('last_access');
                $session->set('last_access', $now);
            }
            return $this->render('e04/index.html.twig', [
                'name' => $name,
                'elapsed' => $elapsed,
            ]);
        }
        catch (DoctrineDBALException $e)
        {
            $this->addFlash('error', 'La base de données est indisponible.');
            return $this->render('e04/error_db.html.twig');
        }
        catch (Exception $e)
        {
            return $this->render('e04/error_db_others.html.twig', [
                'error_message' => 'Erreur inattendue : ' . $e->getMessage(),
                'exception_message' => $e::class,
            ]);
        }
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/e04/sign_out', name: 'e04_sign_out')]
    public function signOut(): void {}

    #[Route('/e04/sign_up', name: 'e04_sign_up')]
    public function createUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            try
            {
                $animals = ['dog', 'cat', 'tiger', 'fox', 'owl', 'koala', 'panda', 'eagle', 'zebra', 'wolf'];
                $username = $user->getUsername();

                foreach ($animals as $animal) 
                {
                    if (strtolower($username) === strtolower("Anonymous $animal"))
                    {
                        $form->get('username')->addError(new FormError('Ce nom est réservé aux utilisateurs anonymes.'));
                        return $this->render('e04/sign_up.html.twig', [
                            'registrationForm' => $form->createView(),
                        ]);
                    }
}
                if ($em->getRepository(User::class)->findOneBy(['username' => $user->getUsername()]))
                    $form->get('username')->addError(new FormError('Ce nom d\'utilisateur est déjà pris.'));
                else
                {
                    $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
                    $user->setRoles(['ROLE_USER']);
                    $em->persist($user);
                    $em->flush();

                    $this->addFlash('success', 'Inscription réussie ! Connecte-toi !');
                    return $this->redirectToRoute('login');
                }
            }
            catch (Throwable $e)
            {
                $this->addFlash('error', 'Erreur lors de la création de l\'utilisateur.');
                return $this->redirectToRoute('e04_index');
            }
        }

        return $this->render('e04/sign_up.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/e04/welcome', name: 'e04_welcome')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted(attribute: 'ROLE_USER')]
    public function welcome(): Response
    {
        try
        {
            return $this->render('e04/welcome.html.twig', [
            'username' => $this->getUser()->getUserIdentifier(),
            ]);
        }
        catch (Exception $e)
        {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'affichage de la page de bienvenue.');
            return $this->redirectToRoute('e04_index');
        }
    }
}