<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class AuthFlashSubscriber implements EventSubscriberInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LogoutEvent::class       => 'onLogout',
        ];
		//LoginFailureEvent::class => 'onLoginFailure',
        // Si tu veux cibler un firewall en particulier, tu peux aussi utiliser les *_MAIN events,
        // mais la méthode ci-dessus est la plus simple et marche pour "main".
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        // message vert après authentification OK
        $event->getRequest()->getSession()->getFlashBag()->add('success', 'Connexion réussie !');
        // Laisse le flow standard continuer (redirection vers target_path / default_target_path)
    }

    /*public function onLoginFailure(LoginFailureEvent $event): void
    {
        // message rouge si identifiants invalides
        $event->getRequest()->getSession()->getFlashBag()->add('error', 'Identifiants invalides.');
        // Le handler par défaut te renverra déjà sur la page de login avec l’erreur.
    }*/

    public function onLogout(LogoutEvent $event): void
    {
        // message vert à la déconnexion
        $event->getRequest()->getSession()->getFlashBag()->add('success', 'Déconnexion réussie !');

        // Option A: laisser la config security.yaml décider de la redirection (logout.target)
        // -> Dans ce cas, ne rien faire ici.

        // Option B: forcer la redirection ici :
        // $url = $this->urlGenerator->generate('e01_index');
        // $event->setResponse(new RedirectResponse($url));
    }
}
