<?php

/**
 * Create a new Silex Application with Twig.  Configure it for debugging.
 * Follows Silex Skeleton pattern.
 */
use Google\Cloud\Samples\Bookshelf\DataModel\Datastore;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\Yaml\Yaml;
date_default_timezone_set('Europe/Amsterdam');

$app = new Application();

// register twig
$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../templates',
    'twig.options' => array(
        'strict_variables' => false,
    ),
));

// parse configuration
$config = getenv('BOOKSHELF_CONFIG') ?:
    __DIR__ . '/../config/' . 'settings.yml';

$app['config'] = Yaml::parse(file_get_contents($config));

// determine the datamodel backend using the app configuration
$app['bookshelf.model'] = function ($app) {
    /** @var array $config */
    $config = $app['config'];
    if (empty($config['bookshelf_backend'])) {
        throw new \DomainException('"bookshelf_backend" must be set in bookshelf config');
    }

    // Data Model
    switch ($config['bookshelf_backend']) {
        case 'datastore':
            return new Datastore(
                $config['google_project_id']
            );
        default:
            throw new \DomainException("Invalid \"bookshelf_backend\" given: $config[bookshelf_backend]. "
                . "Possible values are mysql, postgres, mongodb, or datastore.");
    }
};

// Turn on debug locally
if (in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', 'fe80::1', '::1'])
    || php_sapi_name() === 'cli-server'
) {
    $app['debug'] = true;
} else {
    $app['debug'] = filter_var(
        getenv('BOOKSHELF_DEBUG'),
                               FILTER_VALIDATE_BOOLEAN
    );
}

// add service parameters
$app['bookshelf.page_size'] = 10;

return $app;
