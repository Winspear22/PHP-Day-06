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
        // Ajout d'un flash via la session (et pas addFlash)
        if ($request->hasSession())
            $request->getSession()->getFlashBag()->add('info', 'Vous ne pouvez pas accéder à cette page.');
        $content = $this->twig->render('access_denied.html.twig');
        return new Response($content, 403);
    }
}


?>