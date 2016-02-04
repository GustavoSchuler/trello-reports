<?php
namespace App\Services;

use App\Traits\Cache;

class Members
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

        $member = $this->getCachedData(
            'member',
            $id,
            function () use ($trello, $id) {
                $model = new \Trello\Model\Member($trello);
                $model->setId($id);

                return $model->get();
            }
        );

        return $member->fullName;
    }
}
