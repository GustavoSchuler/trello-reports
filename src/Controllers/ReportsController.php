<?php
namespace App\Controllers;

use App\Models\Card;

class ReportsController extends Controller
{
    /**
     * @var \App\Repositories\CardsRepository
     */
    protected $repo;

    public function __construct($app)
    {
        parent::__construct($app);

        $this->repo = $app['cards.repository'];
    }

    public function indexAction()
    {
        return $this->twig->render('reports/index.twig');
    }

    public function timeAction()
    {
        // Default period, last 7 days.
        $from = (new \DateTime())->sub(new \DateInterval("P7D"))->setTime(0, 0, 0);
        $to   = new \DateTime();

        if ($this->request->get('from')) {
            $from = new \DateTime($this->request->get('from'));
        }
        if ($this->request->get('to')) {
            $to = new \DateTime($this->request->get('to'));
        }

        $cards = $this->repo->getInPeriod($from, $to, $this->session->get('trello-board'));

        // Filter members.
        $team = $this->app['config']['team'];
        $cards = array_filter($cards, function(Card $item) use($team){
            return !empty(array_intersect($item->getDev(), $team));
        });

        return $this->twig->render(
            'reports/time.twig',
            [
                'cards' => $cards,
                'from' => $from,
                'to' => $to,
            ]
        );
    }
}
