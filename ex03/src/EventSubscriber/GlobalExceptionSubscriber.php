<?php

namespace App\EventSubscriber;

use Doctrine\DBAL\Exception as DoctrineDBALException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class GlobalExceptionSubscriber implements EventSubscriberInterface
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // Vérifie si c'est une erreur de base de données
        if ($exception instanceof DoctrineDBALException || str_contains($exception->getMessage(), 'SQLSTATE')) {
            $html = $this->twig->render('bundles/TwigBundle/Exception/error_db.html.twig', [
                'error_message' => 'Erreur critique : impossible de se connecter à la base de données.',
                'exception_message' => $exception->getMessage(),
            ]);

            $event->setResponse(new Response($html, 503));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.exception' => 'onKernelException',
        ];
    }
}
