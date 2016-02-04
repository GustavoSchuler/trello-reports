<?php
namespace App\Controllers;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->twig->render('index.twig');
    }

    /**
     * Select board to be used for reports.
     *
     * @return mixed
     */
    public function listBoardsAction()
    {
        $board = $this->request->get('board');
        if ($board) {
            $this->session->set('trello-board', $board);

            return $this->redirect('GET_reports');
        }

        $model = new \Trello\Model\Member($this->app['trello']);
        $model->setId('me');

        $boards = $model->getBoards();

        return $this->twig->render('board.twig', ['boards' => $boards]);
    }
}
