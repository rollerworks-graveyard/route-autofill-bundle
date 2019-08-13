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

namespace Rollerworks\Bundle\RouteAutofillBundle\Tests\CacheWarmer;

use PHPUnit\Framework\TestCase;
use Rollerworks\Bundle\RouteAutofillBundle\CacheWarmer\RouteRedirectMappingWarmer;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class RouteRedirectMappingWarmerTest extends TestCase
{
    /** @test */
    public function it_warms_up_cache()
    {
        $routesCollection = new RouteCollection();
        $routesCollection->add('webhosting.accounts', self::createRoute('/webhosting/'));
        $routesCollection->add('webhosting.account.ftp.user_list', self::createRoute('/webhosting/{account}/ftp/users/', ['autofill_variables' => true]));
        $routesCollection->add('webhosting.account.account_list', self::createRoute('/webhosting/list', ['autofill_variables' => false]));
        $routesCollection->add('webhosting.account.db.postgres.tables.list', self::createRoute('/webhosting/{account}/db/postgres/{db}/tables', ['autofill_variables' => true]));
        $routesCollection->add('webhosting.account.db.postgres.tables.grants', self::createRoute('/webhosting/{account}/db/postgres/{db}/{id}/grants/', ['autofill_variables' => ['account']]));
        $routesCollection->add('webhosting.account.db.postgres.tables.grants2', self::createRoute('/webhosting/{account}/db/postgres/{db}/{id}/grants2', ['autofill_variables' => 'account , db']));
        $routesCollection->add('ignore.me.please.ok', self::createRoute('{id}/{foo}/bar', ['autofill_variables' => 1]));

        $mappingFile = $this->removeExistingMappingFile();
        $warmer = new RouteRedirectMappingWarmer($this->createRouterService($routesCollection));
        $warmer->warmUp(__DIR__);

        self::assertEquals(
            [
                'webhosting.account.ftp.user_list' => ['account' => true],
                'webhosting.account.db.postgres.tables.list' => ['account' => true, 'db' => true],
                'webhosting.account.db.postgres.tables.grants' => ['account' => true],
                'webhosting.account.db.postgres.tables.grants2' => ['account' => true, 'db' => true],
            ],
            include $mappingFile
        );
    }

    /** @test */
    public function it_warms_up_cache_with_no_routes()
    {
        $mappingFile = $this->removeExistingMappingFile();
        $warmer = new RouteRedirectMappingWarmer($this->createRouterService(new RouteCollection()));
        $warmer->warmUp(__DIR__);

        self::assertEquals([], include $mappingFile);
    }

    private static function createRoute(string $str, array $options = []): Route
    {
        return new Route($str, [], [], $options);
    }

    private function createRouterService(RouteCollection $routesCollection): RouterInterface
    {
        $routerProphecy = $this->prophesize(RouterInterface::class);
        $routerProphecy->getRouteCollection()->willReturn($routesCollection);

        return $routerProphecy->reveal();
    }

    private function removeExistingMappingFile(): string
    {
        $mappingFile = __DIR__.'/route_autofill_mapping.php';

        if (file_exists($mappingFile)) {
            unlink($mappingFile);
        }

        return $mappingFile;
    }
}
