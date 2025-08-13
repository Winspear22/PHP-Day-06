<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
}
