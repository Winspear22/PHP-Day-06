<?php 

namespace App\E04Bundle\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class E04EventSubscriber implements EventSubscriberInterface
{
	private const TTL_SECONDS = 60;
	
	public function __construct(
        private TokenStorageInterface $tokenStorage
    ) {}

	public static function getSubscribedEvents()
	{
		return [ KernelEvents::REQUEST => 'onKernelRequest' ];
	}

	public function onKernelRequest(RequestEvent $event): void
	{
		// 1) Seulement la requête principale
		if (!$event->isMainRequest())
			return;

		$request = $event->getRequest();

		// 2) Ne s’applique que sur l’espace /e04
		if (!str_starts_with($request->getPathInfo(), '/e04'))
			return;

		    // 3) Session
		$session = $request->getSession();
		if (!$session->isStarted())
			$session->start();
		

		// 4) Calcul du temps écoulé
		$now  = time();
		$prev = $session->get('e04.last_request_at'); // int|null
		$secondsSinceLast = is_int($prev) ? max(0, $now - $prev) : 0;

		// 5) Déterminer si connecté
		$token = $this->tokenStorage->getToken();
		$isLoggedIn = $token && \is_object($token->getUser());

		// 6) Gérer le pseudo anonyme si NON connecté
		if (!$isLoggedIn) {
			$currentName = $session->get('e04.anon_name'); // string|null

			// Première visite ou inactivité > TTL → réassigne un nom et reset compteur
			if (!\is_string($currentName) || $secondsSinceLast > self::TTL_SECONDS) {
				// (bonus sécu) rotation d'ID pour éviter fixation de session
				$session->migrate(true);

				$currentName = 'Anonymous ' . $this->randomAnimal();
				$secondsSinceLast = 0; // on repart à 0
				$session->set('e04.anon_name', $currentName);
			}

			// Exposer à la requête pour le contrôleur/Twig
			$request->attributes->set('e04_anon_name', $currentName);
		} else {
			// Connecté → pas de pseudo anonyme
			$request->attributes->set('e04_anon_name', null);
		}

		// 7) Exposer le compteur + maj timestamp
		$request->attributes->set('e04_seconds_since_last', $secondsSinceLast);
		$session->set('e04.last_request_at', $now);
	}

	private function randomAnimal(): string
	{
		static $animals = ['cat','dog','fox','panda','owl','eagle','wolf','bear','lynx','otter'];
		return $animals[array_rand($animals)];
	}

}

?>