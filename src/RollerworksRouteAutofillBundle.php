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

namespace Rollerworks\Bundle\RouteAutofillBundle;

use Rollerworks\Bundle\RouteAutofillBundle\DependencyInjection\DependencyExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class RollerworksRouteAutofillBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new DependencyExtension();
        }

        return $this->extension;
    }

    protected function getContainerExtensionClass(): string
    {
        return DependencyExtension::class;
    }
}
