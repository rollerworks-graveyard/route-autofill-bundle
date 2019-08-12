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

namespace Rollerworks\Bundle\RouteAutofillBundle\DependencyInjection;

use Rollerworks\Bundle\RouteAutofillBundle\AutoFilledUrlGenerator;
use Rollerworks\Bundle\RouteAutofillBundle\CacheWarmer\RouteRedirectMappingWarmer;
use Rollerworks\Bundle\RouteAutofillBundle\EventListener\RouteRedirectResponseListener;
use Rollerworks\Bundle\RouteAutofillBundle\MappingFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

final class DependencyExtension extends Extension
{
    public const EXTENSION_ALIAS = 'rollerworks_route_autofill';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->register(RouteRedirectMappingWarmer::class)
            ->addArgument(new Reference('router.default'))
            ->addTag('kernel.cache_warmer');

        $container->register(AutoFilledUrlGenerator::class)
            ->setAutowired(true)
            ->setArgument(
                '$autoFillMapping',
                (new Definition(MappingFileLoader::class))->setArguments(['%kernel.cache_dir%/route_autofill_mapping.php'])
            );

        $container->register(RouteRedirectResponseListener::class)
            ->addTag('kernel.event_subscriber')
            ->addArgument(new Reference(AutoFilledUrlGenerator::class))
            ->addArgument(new Reference('session'));
    }

    public function getAlias(): string
    {
        return self::EXTENSION_ALIAS;
    }
}
