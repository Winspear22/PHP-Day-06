<?php

namespace App\E02Controller\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class E02Bundle extends AbstractController
{
    /**
     * @Route("/e02controller", name="e02controller_index")
     */
    public function index(): Response
    {
        return new Response("Hello from E02Bundle!");
    }
}
