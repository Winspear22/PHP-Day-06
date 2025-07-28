<?php

namespace App\E01Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class E01Controller extends AbstractController
{
    /**
     * @Route("/e01bundle", name="e01bundle_index")
     */
    public function index(): Response
    {
        return new Response("Hello from E01Controller!");
    }
}
