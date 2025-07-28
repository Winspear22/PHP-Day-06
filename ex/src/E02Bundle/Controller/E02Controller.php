<?php

namespace App\E02Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class E02Controller extends AbstractController
{
    /**
     * @Route("/e02bundle", name="e02bundle_index")
     */
    public function index(): Response
    {
        return new Response("Hello from E02Controller!");
    }
}
