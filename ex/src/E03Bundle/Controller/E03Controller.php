<?php

namespace App\E03Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class E03Controller extends AbstractController
{
    /**
     * @Route("/e03bundle", name="e03bundle_index")
     */
    public function index(): Response
    {
        return new Response("Hello from E03Controller!");
    }
}
