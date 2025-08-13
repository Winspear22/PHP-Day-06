<?php

namespace App\E01Controller\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class E01Bundle extends AbstractController
{
    /**
     * @Route("/e01controller", name="e01controller_index")
     */
    public function index(): Response
    {
        return new Response("Hello from E01Bundle!");
    }
}
