RollerworksRouteAutofillBundle
==============================

The RollerworksRouteAutofillBundle helps with generating routes that follow a
shared prefix with parameters. All end-points at a deeper level always have the
route-parameters of their prefix, and therefor can reuse these when generating
a route that also has this same prefix.

For example: You are current at the following route `/webhosting/{account}/ftp/users/`
and want to generate a URL back to `/webhosting/{account}/`. Because the current
page already has a value for `{account}`, we can easily reuse this.

**Tip:** This technique also works for host-requirements.

But there is more, this bundle also provides a `RouteRedirectResponse` to generate a
redirect response for a route with ease.

What's this good for?
---------------------

Their are times when you cannot provide the required route-parameters,
or simply don't want to be bothered with these details.

The main purpose of this bundle is convenience, you can always solve these
challenges differently but that also requires more work.

Requirements
------------

You need at least PHP 7.1 and the Symfony FrameworkBundle.

Installation
------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ php composer.phar require rollerworks/route-autowiring-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.


Basic usage
-----------

First of auto-filling the route parameters does not happen automatically,
only when you use the `AutoFilledUrlGenerator` _(compatible with the Symfony
Routing system)_ auto-filling is provided.

`$container->get(AutoFilledUrlGenerator::class);`

**Note:** The `RouteRedirectResponse` already uses this route-generator.

Secondly, you need to set the `autofill_variables` option for your routes
to enable the auto-filling of specific parameters.

**Caution:** This option must be set per route, provide either an array or
string with named separated by a `,` (`account,id`).

### RouteRedirectResponse

The RouteRedirectResponse is provided to directly generate a redirect response
for a route:

```php
use Rollerworks\Bundle\RouteAutofillBundle\Response\RouteRedirectResponse;

// (string $route, array $parameters = [], int $status = 302)
return new RouteRedirectResponse('route-name');
```

That's it. 

License
-------

All contents of this package are released under the [MIT license](LICENSE).
