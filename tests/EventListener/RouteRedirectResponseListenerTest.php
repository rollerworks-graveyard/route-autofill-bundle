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

namespace Rollerworks\Bundle\RouteAutofillBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Rollerworks\Bundle\RouteAutofillBundle\EventListener\RouteRedirectResponseListener;
use Rollerworks\Bundle\RouteAutofillBundle\Response\RouteRedirectResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
final class RouteRedirectResponseListenerTest extends TestCase
{
    /** @test */
    public function it_ignores_other_responses(): void
    {
        $urlGeneratorProphecy = $this->prophesize(UrlGeneratorInterface::class);
        $urlGeneratorProphecy->generate(Argument::any())->shouldNotBeCalled();
        $urlGenerator = $urlGeneratorProphecy->reveal();

        $session = $this->createNotUsedSession();

        $listener = new RouteRedirectResponseListener($urlGenerator, $session);
        $event = $this->createEvent(false);

        $listener->onKernelView($event);

        self::assertFalse($event->hasResponse());
        self::assertFalse($event->isPropagationStopped());
    }

    private function createEvent($result): ViewEvent
    {
        return new ViewEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            $result
        );
    }

    private function assertResponseIsRedirect($response, string $expectedTargetUrl, int $expectedStatus = 302): void
    {
        /* @var RedirectResponse $response */
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertEquals($expectedTargetUrl, $response->getTargetUrl());
        self::assertEquals($expectedStatus, $response->getStatusCode());
    }

    private function createNotUsedSession(): SessionWithFlashes
    {
        $sessionProphecy = $this->prophesize(SessionWithFlashes::class);
        $sessionProphecy->start()->shouldNotBeCalled();
        $sessionProphecy->getFlashBag()->shouldNotBeCalled();

        return $sessionProphecy->reveal();
    }

    /** @test */
    public function it_sets_a_redirect_response(): void
    {
        $urlGeneratorProphecy = $this->prophesize(UrlGeneratorInterface::class);
        $urlGeneratorProphecy
            ->generate('foobar', ['he' => 'bar'])
            ->willReturn('https://park-manager.com/webhosting');
        $urlGenerator = $urlGeneratorProphecy->reveal();

        $session = $this->createNotUsedSession();

        $listener = new RouteRedirectResponseListener($urlGenerator, $session);
        $event = $this->createEvent(new RouteRedirectResponse('foobar', ['he' => 'bar']));

        $listener->onKernelView($event);

        $this->assertResponseIsRedirect($event->getResponse(), 'https://park-manager.com/webhosting');
    }

    /** @test */
    public function it_sets_a_redirect_response_with_custom_status(): void
    {
        $urlGeneratorProphecy = $this->prophesize(UrlGeneratorInterface::class);
        $urlGeneratorProphecy
            ->generate('foobar', ['he' => 'bar'])
            ->willReturn('https://park-manager.com/webhosting');
        $urlGenerator = $urlGeneratorProphecy->reveal();

        $session = $this->createNotUsedSession();

        $listener = new RouteRedirectResponseListener($urlGenerator, $session);
        $event = $this->createEvent(RouteRedirectResponse::permanent('foobar', ['he' => 'bar']));

        $listener->onKernelView($event);

        $this->assertResponseIsRedirect(
            $event->getResponse(),
            'https://park-manager.com/webhosting',
            RedirectResponse::HTTP_MOVED_PERMANENTLY
        );
    }

    /** @test */
    public function it_sets_a_redirect_response_and_handles_flashes(): void
    {
        $urlGeneratorProphecy = $this->prophesize(UrlGeneratorInterface::class);
        $urlGeneratorProphecy
            ->generate('foobar', ['he' => 'bar'])
            ->willReturn('https://park-manager.com/webhosting');
        $urlGenerator = $urlGeneratorProphecy->reveal();

        $session = $this->createSessionHandlerWithFlashes();

        $listener = new RouteRedirectResponseListener($urlGenerator, $session);
        $event = $this->createEvent(
            RouteRedirectResponse::toRoute('foobar', ['he' => 'bar'])
                ->withFlash('success', 'Perfect {id}', ['id' => 200])
                ->withFlash('error', 'Bag of cash gone')
        );

        $listener->onKernelView($event);

        $this->assertResponseIsRedirect($event->getResponse(), 'https://park-manager.com/webhosting');
        self::assertEquals(
            [
                'success' => [
                    ['message' => 'Perfect {id}', 'parameters' => ['id' => 200]],
                ],
                'error' => ['Bag of cash gone'],
            ],
            $session->getFlashBag()->peekAll()
        );
    }

    /** @test */
    public function it_sets_a_redirect_response_and_ignores_flashes_if_not_supported(): void
    {
        $urlGeneratorProphecy = $this->prophesize(UrlGeneratorInterface::class);
        $urlGeneratorProphecy
            ->generate('foobar', ['he' => 'bar'])
            ->willReturn('https://park-manager.com/webhosting');
        $urlGenerator = $urlGeneratorProphecy->reveal();

        $sessionProphecy = $this->prophesize(SessionInterface::class);
        $session = $sessionProphecy->reveal();

        $listener = new RouteRedirectResponseListener($urlGenerator, $session);
        $event = $this->createEvent(
            RouteRedirectResponse::toRoute('foobar', ['he' => 'bar'])
                ->withFlash('success', 'Perfect {id}', ['id' => 200])
                ->withFlash('error', 'Bag of cash gone')
        );

        $listener->onKernelView($event);

        $this->assertResponseIsRedirect($event->getResponse(), 'https://park-manager.com/webhosting');
    }

    private function createSessionHandlerWithFlashes(): SessionWithFlashes
    {
        $sessionProphecy = $this->prophesize(SessionWithFlashes::class);
        $sessionProphecy->getFlashBag()->willReturn(new FlashBag());

        return $sessionProphecy->reveal();
    }
}

interface SessionWithFlashes extends SessionInterface
{
    public function getFlashBag(): FlashBagInterface;
}
