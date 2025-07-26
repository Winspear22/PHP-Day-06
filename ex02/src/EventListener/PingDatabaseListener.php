<?php

namespace App\EventListener;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;

class PingDatabaseListener
{
    private $connection;
    private $twig;

    public function __construct(Connection $connection, TwigEnvironment $twig)
    {
        $this->connection = $connection;
        $this->twig = $twig;
    }

    public function onKernelRequest(RequestEvent $event)
    {
		file_put_contents('/tmp/ping-db.txt', date('c') . " - PING\n", FILE_APPEND);

        if (!$event->isMainRequest())
            return;
        try
		{
            $this->connection->executeQuery('SELECT 1');
        } 
		catch (\Throwable $e)
		{
            $html = $this->twig->render('bundles/TwigBundle/Exception/error_db.html.twig', [
                'error_message' => 'Impossible de se connecter à la base de données.',
                'exception_message' => $e->getMessage(),
            ]);
            $response = new Response($html, 503);
            $event->setResponse($response);
        }
    }
}
