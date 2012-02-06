# Laravel Documentation API Bundle

This bundle provides access to the Github hosted Laravel documentation.

## Installation

    php artisan bundle:install laravel-docs-api

The github-api bundle will also be installed since it is a dependency, so be sure to add both to your bundles.php configuration.

## Usage

Get an instance of the API:

    $api = IoC::resolve('laravel.docs.api');

Request a page:

	// From master branch...
    $markdown = $api->page('usage', 'auth');

    // From develop branch...
    $markdown = $api->page('usage', 'auth', 'develop');