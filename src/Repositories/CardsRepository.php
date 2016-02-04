<?php
namespace App\Repositories;

use App\Models\Card;
use App\Traits\Cache;
use Trello\Client;
use Trello\Model\Card as TrelloCard;

class CardsRepository
{
    use Cache;

    protected $trello;
    protected $cacheDir;

    public function __construct(Client $trello, $config)
    {
        $this->trello   = $trello;
        $this->cacheDir = $config['dbCacheDir'];
    }

    public function getCards($ids)
    {
        return array_map(
            function ($id) {
                return new Card($this->getCachedCard($id));
            },
            $ids
        );
    }

    /**
     * @param $listId
     *
     * @return Card[]
     */
    public function getCardsInList($listId)
    {
        $trello = $this->trello;

        $cards = $this->getCachedData(
            'list',
            $listId,
            function () use ($listId, $trello) {
                $model = new \Trello\Model\Lane($trello);
                $model->setId($listId);

                return $model->getCards();
            }
        );

        $ids = array_map(
            function ($card) {
                return $card->id;
            },
            $cards
        );

        return array_map(
            function ($id) {
                return new Card($this->getCachedCard($id));
            },
            $ids
        );
    }

    protected function getCachedCard($id)
    {
        $trello = $this->trello;

        $card = $this->getCachedData(
            'card',
            $id,
            function () use ($id, $trello) {
                $model = new TrelloCard($trello);
                $model->setId($id);

                $card          = $model->get();
                $card->actions = $model->getPath('actions');

                return $card;
            }
        );

        return $card;
    }

    public function getInPeriod(\DateTime $from, \DateTime $to, $board)
    {
        $cards = $this->getCardsInBoard($board);

        $cards = array_filter($cards, function(Card $item) use($from, $to){
            $date = $item->getLastChangeDate();
            return $date >= $from && $date <= $to;
        });

        return $cards;
    }

    /**
     * @param $board
     *
     * @return Card[]
     */
    public function getCardsInBoard($board)
    {
        $trello = $this->trello;

        $cards = $this->getCachedData(
            'board',
            $board,
            function () use ($board, $trello) {
                $model = new \Trello\Model\Board($trello);
                $model->setId($board);

                return $model->getCards();
            }
        );

        $ids = array_map(
            function ($card) {
                return $card->id;
            },
            $cards
        );

        return array_map(
            function ($id) {
                return new Card($this->getCachedCard($id));
            },
            $ids
        );
    }
}
