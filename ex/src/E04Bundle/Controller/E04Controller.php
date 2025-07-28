<?php

namespace App\E04Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class E04Controller extends AbstractController
{
    /**
     * @Route("/e04bundle", name="e04bundle_index")
     */
    public function index(): Response
    {
        return new Response("Hello from E04Controller!");
    }
}
