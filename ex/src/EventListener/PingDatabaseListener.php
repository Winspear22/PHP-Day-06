<?php

namespace App\EventListener;

use Throwable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Twig\Environment as TwigEnvironment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class PingDatabaseListener
{
    private Connection $connection;
    private TwigEnvironment $twig;

    public function __construct(Connection $connection, TwigEnvironment $twig)
    {
        $this->connection = $connection;
        $this->twig = $twig;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest())
            return;
        try
		{
            // Test de connexion à la base
            $this->connection->executeQuery('SELECT 1');

            // Test de la présence de la table "user"
            $this->connection->executeQuery('SELECT COUNT(*) FROM user');
        }
		catch (TableNotFoundException $e)
		{
            // Cas spécifique : table manquante
            $html = $this->twig->render('bundles/TwigBundle/Exception/error_db.html.twig', [
                'error_message' => 'La base est connectée, mais la table "user" est manquante.',
                'exception_message' => $e->getMessage(),
            ]);
            $event->setResponse(new Response($html, 503));
        }
		catch (Throwable $e)
		{
            // Cas général : base de données inaccessible ou autre
            $html = $this->twig->render('bundles/TwigBundle/Exception/error_db.html.twig', [
                'error_message' => 'Erreur de base de données.',
                'exception_message' => $e->getMessage(),
            ]);
            $event->setResponse(new Response($html, 503));
        }
    }
}