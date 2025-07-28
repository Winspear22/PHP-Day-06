<?php

namespace App\E05Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class E05Controller extends AbstractController
{
    /**
     * @Route("/e05bundle", name="e05bundle_index")
     */
    public function index(): Response
    {
        return new Response("Hello from E05Controller!");
    }
}
