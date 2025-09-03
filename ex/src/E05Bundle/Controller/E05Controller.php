<?php

namespace App\E05Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class E05Controller extends AbstractController
{
    #[Route('/e05', name: 'e05_index')]
    public function index(): Response
    {
        $message = "popo";
        return $this->render('e01/index.html.twig', [
            'message' => $message
        ]);
    }
}
