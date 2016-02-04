<?php
namespace App\Controllers;

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

        // TODO: Add form to select period.

        $cards = $this->repo->getInPeriod($from, $to, $this->session->get('trello-board'));

        return $this->twig->render('reports/time.twig', ['cards' => $cards]);
    }
}
