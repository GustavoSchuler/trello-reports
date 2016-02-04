<?php
use App\Repositories\CardsRepository;
use Silex\Application;

/* @var $app \Silex\Application */

$app['trello'] = $app->share(
    function ($app) {
        return new Trello\Client($app['config']['api-key']);
    }
);

$app['twig'] = $app->share(
    $app->extend(
        'twig',
        function (\Twig_Environment $twig, Application $app) {
            $twig->addFunction(
                new \Twig_SimpleFunction(
                    'trello_label',
                    function ($id) use ($app) {
                        static $cache;
                        if (!$cache) {
                            $cache = [];
                        }

                        if (!isset($cache[$id])) {
                            $label      = $app['trello']->get('labels/'.$id);
                            $cache[$id] = $label['name'];
                        }

                        return $cache[$id];
                    }
                )
            );

            $twig->addFunction(
                new \Twig_SimpleFunction(
                    'trello_user',
                    function ($id) use ($app) {
                        static $cache;
                        if (!$cache) {
                            $cache = [];
                        }

                        if (!isset($cache[$id])) {
                            $user       = $app['trello']->get('members/'.$id);
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

$app['cards.repository'] = $app->share(function($app) {
    return new CardsRepository($app['trello'], $app['config']);
});

$app['members'] = $app->share(function($app) {
    return new \App\Services\Members($app['trello'], $app['config']['dbCacheDir']);
});
