<?php

namespace App\E07Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class E07Controller extends AbstractController
{
    /**
     * @Route("/e07bundle", name="e07bundle_index")
     */
    public function index(): Response
    {
        return new Response("Hello from E07Controller!");
    }
}
