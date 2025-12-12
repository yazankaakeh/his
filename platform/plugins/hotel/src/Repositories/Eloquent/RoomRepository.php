<?php

namespace Botble\Hotel\Repositories\Eloquent;

use Botble\Hotel\Facades\HotelHelper;
use Botble\Hotel\Repositories\Interfaces\RoomInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;

class RoomRepository extends RepositoriesAbstract implements RoomInterface
{
    public function getRooms(array $filters = [], array $params = [])
    {
        $filters = array_merge([
            'keyword' => null,
            'room_category_id' => null,
            'hotel_id' => null,
            'location_id' => null,
        ], $filters);

        $filters = HotelHelper::getRoomFilters($filters);

        $params = array_merge([
            'condition' => [],
            'order_by' => [
                'created_at' => 'DESC',
                'order' => 'ASC',
            ],
            'take' => null,
            'paginate' => [
                'per_page' => 10,
                'current_paged' => 1,
            ],
            'with' => [
                'amenities',
                'slugable',
            ],
        ], $params);

        $this->model = $this->originalModel;

        if ($filters['keyword'] !== null) {
            $this->model = $this->model
                ->where('name', 'LIKE', '%' . $filters['keyword'] . '%');
        }

        if ($filters['room_category_id'] !== null) {
            $this->model = $this->model->where('room_category_id', $filters['room_category_id']);
        }

        if ($filters['hotel_id'] !== null) {
            $this->model = $this->model->where('hotel_id', $filters['hotel_id']);
        }

        if ($filters['location_id'] !== null) {
            $this->model = $this->model->whereHas('hotel', function ($query) use ($filters) {
                $query->where('location_id', $filters['location_id']);
            });
        }

        $this->model->wherePublished();

        return $this->advancedGet($params);
    }

    public function getRelatedRooms(int $roomId, int $limit = 4, array $params = [])
    {
        $this->model = $this->originalModel;
        $this->model = $this->model
            ->where('id', '<>', $roomId);

        $params = array_merge([
            'condition' => [],
            'order_by' => [
                'created_at' => 'DESC',
            ],
            'take' => $limit,
            'paginate' => [
                'per_page' => 12,
                'current_paged' => 1,
            ],
            'with' => [
                'amenities',
                'slugable',
            ],
        ], $params);

        return $this->advancedGet($params);
    }
}
