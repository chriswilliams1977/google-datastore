<?php

require_once __DIR__ . '/../vendor/autoload.php';

/** @var Silex\Application $app */
$app = require __DIR__ . '/../src/app.php';
require __DIR__ . '/../src/controllers.php';

$app->run();
