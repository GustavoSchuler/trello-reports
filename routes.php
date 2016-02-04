<?php
/* @var $app \Silex\Application */

$app->get('/', "default.controller:indexAction");
$app->get('/logout', 'auth.controller:logoutAction');
$app->get('/token', 'auth.controller:tokenAction');
$app->get('/board', "default.controller:listBoardsAction");
$app->get('/reports', "reports.controller:indexAction");
$app->get('/reports/time', "reports.controller:timeAction");

$app->get(
    '/board/{id}',
    function ($id) use ($app, $config) {
        $model = new Trello\Model\Board($app['trello']);
        $model->setId($id);

        $cards = $model->getLists();
//
//        $cards = array_filter(
//            $cards,
//            function ($item) use ($config) {
//                return array_intersect($item->idMembers, $config['team']);
//            }
//        );

        foreach ($cards as $card) {
//            echo '<p>'.$card->name. ' - ' . $card->dateLastActivity. '</p>';
            var_dump($card);
        }
        var_dump(count($cards));
        die();

        return $app['twig']->render('board.twig', ['boards' => $boards]);
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

$app->get(
    '/card/{id}',
    function ($id) use ($app) {
        $model = new Trello\Model\Card($app['trello']);
        $model->setId($id);

        $card = $model->getPath('actions');

        // Get task type: $card->labels

        var_dump($card);
//        return $app['twig']->render('reports/card.twig', ['card' => $card]);
        die();
    }
);


/// Debug
$app->get(
    '/_debug/routes',
    function () use ($app) {
        /* @var $routes \Symfony\Component\Routing\RouteCollection */
        $routes = $app['routes'];

        foreach ($routes as $r => $v) {
            echo "<p>$r -- {$v->getPath()}</p>";
        }
        die();
    }
);
