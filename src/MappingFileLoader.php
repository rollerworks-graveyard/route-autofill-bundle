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

/** @internal */
final class MappingFileLoader
{
    private $filename;
    private $mapping;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public static function fromArray(array $mapping): self
    {
        $loader = new self('nope');
        $loader->mapping = $mapping;

        return $loader;
    }

    public function all()
    {
        if (null === $this->mapping) {
            $this->mapping = include $this->filename;
        }

        return $this->mapping;
    }
}
