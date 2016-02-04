<?php
use App\Controllers as C;

/* @var $app \Silex\Application */

$app['reports.controller'] = $app->share(function() use ($app) {
    return new C\ReportsController($app);
});

$app['default.controller'] = $app->share(function() use ($app) {
    return new C\DefaultController($app);
});

$app['auth.controller'] = $app->share(function() use ($app) {
    return new C\AuthController($app);
});
