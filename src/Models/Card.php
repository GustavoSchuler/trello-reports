<?php
namespace App\Models;

class Card
{
    protected $data;

    public function __construct($cardInfo)
    {
        $this->data = $cardInfo;
    }

    public function getName()
    {
        return $this->data->name;
    }

    public function getLastChangeDate()
    {
        $result = null;

        $updates = array_filter(
            $this->data->actions,
            function ($data) {
                return $data['type'] == 'updateCard';
            }
        );

        if (count($updates)) {
            $updates = array_values($updates);
            $result  = new \DateTime($updates[0]['date']);
        }

        return $result;
    }

    public function getDev()
    {
        $result = null;

        // Moved to 'Doing'
        $updates = array_filter(
            $this->data->actions,
            function ($update) {
                return $update['type'] == 'updateCard' && $update['data']['listAfter']['name'] == 'Doing';
            }
        );

        if (count($updates)) {
            $updates = array_values($updates);
            $dev     = end($updates)['memberCreator']['id'];
            // Check if is still in members list.
            if (in_array($dev, $this->data->idMembers)) {
                $result = [$dev];
            }
        }

        // Get dev by "Assigned"
        if (!$result) {
            $result = $this->data->idMembers;
        }

        // Moved to 'Live'
        if (!$result) {
            $updates = array_filter(
                $this->data->actions,
                function ($update) {
                    return $update['type'] == 'updateCard' && $update['data']['listAfter']['name'] == 'Live';
                }
            );

            if (count($updates)) {
                $updates = array_values($updates);
                $result  = [end($updates)['memberCreator']['id']];
            }
        }

        return $result;
    }

    public function getList()
    {
        return $this->data->idList;
    }
}
