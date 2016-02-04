<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

require_once __DIR__.'/vendor/autoload.php';

$config = require_once __DIR__.'/config.php';

$app          = new Application();
$app['debug'] = true;

$app['config'] = $config;

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), ['twig.path' => __DIR__.'/views']);

require_once __DIR__.'/services.php';
require_once __DIR__.'/controllers.php';

// Init Trello client. Redirect to Trello if no token in session.
$app->before(
    function (Request $request, Application $app) {
        $trelloToken = $app['session']->get('trello-token');
        $tokenUrl    = $app['url_generator']->generate('GET_token', [], UrlGeneratorInterface::ABSOLUTE_URL);

        // Redirect to Trello if not token set and URL is not /token.
        $currentUrl = $request->getUri();
        $len        = strlen($request->getQueryString());
        if ($len) {
            $len++;
            $currentUrl = substr($currentUrl, 0, -$len);
        }

        if (!$trelloToken && $currentUrl != $tokenUrl) {
            $url = $app['trello']->getAuthorizationUrl('Trello Reports', $tokenUrl);

            return $app->redirect($url);
        }

        $app['trello']->setAccessToken($trelloToken);
    }
);

require_once __DIR__.'/routes.php';

$app->run();
