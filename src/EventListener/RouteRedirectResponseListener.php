<?php

declare(strict_types=1);

/*
 * This file is part of the Rollerworks RouteAutofillBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\RouteAutofillBundle\EventListener;

use Rollerworks\Bundle\RouteAutofillBundle\Response\RouteRedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RouteRedirectResponseListener implements EventSubscriberInterface
{
    private $urlGenerator;

    /** @var SessionInterface|Session */
    private $session;

    public function __construct(UrlGeneratorInterface $urlGenerator, SessionInterface $session)
    {
        $this->urlGenerator = $urlGenerator;
        $this->session = $session;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event): void
    {
        $result = $event->getControllerResult();

        if (!$result instanceof RouteRedirectResponse) {
            return;
        }

        $flashes = $result->getFlashes();

        if (\count($flashes) > 0 && method_exists($this->session, 'getFlashBag')) {
            $flashBag = $this->session->getFlashBag();

            foreach ($flashes as $flash) {
                $flashBag->add($flash[0], null === $flash[2] ? $flash[1] : [
                    'message' => $flash[1],
                    'parameters' => $flash[2],
                ]);
            }
        }

        $event->setResponse(
            new RedirectResponse(
                $this->urlGenerator->generate($result->getRoute(), $result->getParameters()),
                $result->getStatus()
            )
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => 'onKernelView'];
    }
}
