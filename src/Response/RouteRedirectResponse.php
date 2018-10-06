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

namespace Rollerworks\Bundle\RouteAutofillBundle\Response;

use Symfony\Component\HttpFoundation\Response;

class RouteRedirectResponse
{
    protected $route;
    protected $parameters = [];
    protected $status = 302;

    public function __construct(string $route, array $parameters = [], int $status = 302)
    {
        $this->route = $route;
        $this->parameters = $parameters;
        $this->status = $status;
    }

    public static function permanent(string $route, array $parameters = [])
    {
        return new static($route, $parameters, Response::HTTP_MOVED_PERMANENTLY);
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
