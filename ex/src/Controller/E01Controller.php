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
        return $this->render('e01/index.html.twig');
    }
}
