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

namespace Rollerworks\Bundle\RouteAutofillBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\Routing\CompiledRoute;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use function array_fill_keys;
use function sprintf;
use function var_export;

final class RouteRedirectMappingWarmer extends CacheWarmer
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * This cache warmer is not optional, missing the RouteRedirect Mappings
     * will cause a fatal error on routes that expect this functionality.
     *
     * @return false
     */
    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp($cacheDir): void
    {
        $mapping = [];

        /** @var Route $route */
        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            $autoFillVariables = $route->getOption('autofill_variables');

            if (null === $autoFillVariables || false === $autoFillVariables) {
                continue;
            }

            $compiledRoute = $route->compile();
            $variableNames = $this->getVariableNames($autoFillVariables, $compiledRoute);

            if ($variableNames === []) {
                continue;
            }

            // Use a boolean value to speed-up the array look-up process
            $mapping[$routeName] = array_fill_keys($variableNames, true);
        }

        $this->writeCacheFile($cacheDir.'/route_autofill_mapping.php', sprintf("<?php return %s;\n", self::export($mapping)));
    }

    /**
     * @param string|array|bool $autoFillVariables
     */
    private function getVariableNames($autoFillVariables, CompiledRoute $compiledRoute): array
    {
        if (true === $autoFillVariables) {
            return $compiledRoute->getVariables();
        }

        if (\is_string($autoFillVariables)) {
            return array_map('trim', explode(',', $autoFillVariables));
        }

        return \is_array($autoFillVariables) ? $autoFillVariables : [];
    }

    /**
     * @see https://github.com/symfony/symfony/blob/4.4/src/Symfony/Component/VarExporter/Internal/Exporter.php
     *
     * @author Nicolas Grekas <p@tchwork.com>
     */
    private static function export($value, string $indent = '')
    {
        switch (true) {
            case \is_int($value) || \is_float($value) || \is_string($value): return var_export($value, true);
            case [] === $value: return '[]';
            case false === $value: return 'false';
            case true === $value: return 'true';
            case null === $value: return 'null';
            case '' === $value: return "''";
        }

        $subIndent = $indent.'    ';

        if (\is_array($value)) {
            $j = -1;
            $code = '';
            foreach ($value as $k => $v) {
                $code .= $subIndent;
                if (!\is_int($k) || 1 !== $k - $j) {
                    $code .= self::export($k, $subIndent).' => ';
                }
                if (\is_int($k) && $k > $j) {
                    $j = $k;
                }
                $code .= self::export($v, $subIndent).",\n";
            }

            return "[\n".$code.$indent.']';
        }

        throw new \UnexpectedValueException(sprintf('Cannot export value of type "%s".', \is_object($value) ? \get_class($value) : \gettype($value)));
    }
}
