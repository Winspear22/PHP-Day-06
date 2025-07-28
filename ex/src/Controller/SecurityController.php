<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        return $this->render('security/login.html.twig');
    }

    #[Route('/need-auth', name: 'need_auth')]
    public function needAuth(): Response
    {
        return $this->render('security/need_auth.html.twig');
    }
}