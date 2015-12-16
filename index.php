<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

require_once __DIR__.'/vendor/autoload.php';

$config = require_once __DIR__.'/config.php';

$app = new Application();
$app['debug'] = true;

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(
    new Silex\Provider\TwigServiceProvider(),
    [
        'twig.path' => __DIR__.'/views',
    ]
);

$app['trello'] = $app->share(
    function () use ($config) {
        return new Trello\Client($config['api-key']);
    }
);

$app['twig'] = $app->share(
    $app->extend(
        'twig',
        function (\Twig_Environment $twig, Application $app) {
            $twig->addFunction(
                new \Twig_SimpleFunction(
                    'trello_label',
                    function ($id) use ($app){
                        static $cache;
                        if (!$cache) {
                            $cache = [];
                        }

                        if (!isset($cache[$id])) {
                            $label = $app['trello']->get('labels/'.$id);
                            $cache[$id] = $label['name'];
                        }

                        return $cache[$id];
                    }
                )
            );

            $twig->addFunction(
                new \Twig_SimpleFunction(
                    'trello_user',
                    function ($id) use ($app){
                        static $cache;
                        if (!$cache) {
                            $cache = [];
                        }

                        if (!isset($cache[$id])) {
                            $user = $app['trello']->get('members/'.$id);
                            $cache[$id] = $user['fullName'];
                        }

                        return $cache[$id];
                    }
                )
            );

            return $twig;
        }
    )
);

// Init Trello client. Redirect to Trello if no token in session.
$app->before(
    function (Request $request, Application $app) {
        $trelloToken = $app['session']->get('trello-token');
        $tokenUrl = $app['url_generator']->generate('GET_token', [], UrlGeneratorInterface::ABSOLUTE_URL);

        // Redirect to Trello if not token set and URL is not /token.
        $currentUrl = $request->getUri();
        $len = strlen($request->getQueryString());
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

// Blank page.
$app->get(
    '/',
    function () use ($app) {
        return $app['twig']->render('index.twig');
    }
);

// Logout
$app->get(
    '/logout',
    function () use ($app) {
        $app['session']->clear();

        return $app->redirect($app['url_generator']->generate('GET_'));
    }
);

// Save Trello token into session, workaround for token as fragment.
$app->get(
    '/token',
    function () use ($app, $config) {
        $token = $app['request']->get('token');
        if ($token) {
            $app['session']->set('trello-token', $token);

            return $app->redirect($app['url_generator']->generate('GET_'));
        }

        return $app['twig']->render('token.twig', ['apikey' => $config['api-key']]);
    }
);

// Select board to be used for reports.
$app->get(
    '/board',
    function () use ($app) {
        $board = $app['request']->get('board');
        if ($board) {
            $app['session']->set('trello-board', $board);

            return $app->redirect($app['url_generator']->generate('GET_reports'));
        }

        $model = new Trello\Model\Member($app['trello']);
        $model->setId('me');

        $boards = $model->getBoards();

        return $app['twig']->render('board.twig', ['boards' => $boards]);
    }
);


$app->get(
    '/reports',
    function () use ($app) {
        return $app['twig']->render('reports.twig');
    }
);

$app->get(
    '/cards',
    function () use ($app) {
        $model = new Trello\Model\Board($app['trello']);
        $model->setId($app['session']->get('trello-board'));

        $cards = $model->getCards();

        return $app['twig']->render('reports/cards.twig', ['cards' => $cards]);
    }
);

$app->run();
