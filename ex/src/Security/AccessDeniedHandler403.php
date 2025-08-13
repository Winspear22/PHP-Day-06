<?php 

// src/Security/AccessDeniedHandler.php
namespace App\Security;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler403 implements AccessDeniedHandlerInterface
{
    public function __construct(private Environment $twig) {}

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        $content = $this->twig->render('need_auth.html.twig');
        return new Response($content, 403);
    }
}


?>