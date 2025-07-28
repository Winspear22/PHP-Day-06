<?php

namespace App\E06Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class E06Controller extends AbstractController
{
    /**
     * @Route("/e06bundle", name="e06bundle_index")
     */
    public function index(): Response
    {
        return new Response("Hello from E06Controller!");
    }
}
