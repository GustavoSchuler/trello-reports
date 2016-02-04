<?php
namespace App\Services;

use App\Traits\Cache;

class Lane
{
    use Cache;

    protected $trello;
    protected $cacheDir;

    public function __construct($trello, $cacheDir)
    {
        $this->trello   = $trello;
        $this->cacheDir = $cacheDir;
    }

    public function getName($id)
    {
        $trello = $this->trello;
        $id     = trim($id);

        if (!$id) {
            return null;
        }

        $list = $this->getCachedData(
            'list',
            $id,
            function () use ($trello, $id) {
                $model = new \Trello\Model\Lane($trello);
                $model->setId($id);

                return $model->get();
            }
        );

        return $list->name;
    }
}
