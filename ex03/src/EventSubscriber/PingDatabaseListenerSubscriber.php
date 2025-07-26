<?php

namespace App\EventSubscriber;

use Doctrine\DBAL\Connection;
use Twig\Environment as TwigEnvironment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class PingDatabaseListenerSubscriber implements EventSubscriberInterface
{
    private Connection $connection;
    private TwigEnvironment $twig;

    public function __construct(Connection $connection, TwigEnvironment $twig)
    {
        $this->connection = $connection;
        $this->twig = $twig;
    }
    public function onRequestEvent(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        try {
            $this->connection->executeQuery('SELECT 1');
        } catch (\Throwable $e) {
            $html = $this->twig->render('bundles/TwigBundle/Exception/error_db.html.twig', [
                'error_message' => 'Impossible de se connecter à la base de données.',
                'exception_message' => $e->getMessage(),
            ]);
            $event->setResponse(new Response($html, 503));
        }
    }


    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onRequestEvent',
        ];
    }
}
