<?php
/**
 * Laravel Documentation API Bundle
 *
 * @author  Taylor Otwell <taylorotwell@gmail.com>
 * @version 1.0.0
 * @link    http://github.com/taylorotwell/laravel-docs-api
 */

// Start the Github API bundle...
Bundle::start('github-api');

// Register the model directory for auto-loading...
Autoloader::namespaces(array(
	'Laravel\\Docs' => __DIR__.DS.'models',
));

// Register the API in the IoC container...
IoC::register('laravel.docs.api', function()
{
	return new Laravel\Docs\API(new Github_Client, Cache::driver());
});