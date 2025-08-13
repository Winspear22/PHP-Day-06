<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class E01Controller extends AbstractController
{
    #[Route('/e01', name: 'e01_index')]
    public function index(): Response
    {
		if ($this->getUser())
            $message = 'Bienvenue sur la page E01 (Ceci est un message du controller).';
		else
            $message = 'Bienvenue, visiteur ! (Ceci est un message du controller).';
        return $this->render('e01/index.html.twig', [
            'message' => $message,
        ]);
    }
    
    #[Route('/e01/need_auth', name: 'e01_need_auth')]
    public function need_auth(): Response
    {
        return $this->render('need_auth.html.twig', [
            'message' => 'Vous n\'êtes pas connecté ! (Ceci est un message du controller).',
        ]);
    }
	
	#[Route('/e01/welcome', name: 'e01_welcome')]
    #[IsGranted('ROLE_USER')]
    public function welcome(): Response
    {
        return $this->render('e01/welcome.html.twig', [
            'message' => 'Bienvenue sur la page Welcome ! (Ceci est un message du controller).',
        ]);
    }
}
