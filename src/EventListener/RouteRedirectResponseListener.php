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
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RouteRedirectResponseListener implements EventSubscriberInterface
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event): void
    {
        $result = $event->getControllerResult();

        if (!$result instanceof RouteRedirectResponse) {
            return;
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
