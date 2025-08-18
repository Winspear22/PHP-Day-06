<?php

namespace App\E04Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class E04Controller extends AbstractController
{
    #[Route('/e04', name: 'e04_index')]
    public function index(Request $request): Response
    {
        $message = 'Bienvenue, visiteur ! (Ceci est un message du controller).';
        return $this->render('e01/index.html.twig', [
            'message' =>$message,
            'exercise'          => 'E04',
            'e04_seconds_since' => (int) $request->attributes->get('e04_seconds_since_last', 0),
            'e04_anon_name'     => $request->attributes->get('e04_anon_name'),
        ]);

    }
}
