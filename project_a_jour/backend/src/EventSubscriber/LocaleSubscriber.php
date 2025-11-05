<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(private string $defaultLocale = 'fr') {}

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Ignore API routes to avoid session usage in stateless firewalls
        if (str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        // Always obtain the session (this will initialize it if needed)
        $session = $request->getSession();
        if ($locale = $request->query->get('_locale')) {
            $session->set('_locale', $locale);
        }

        $request->setLocale($session->get('_locale', $this->defaultLocale));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}


