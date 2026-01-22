<?php

namespace App\Repositories\Transportation;

use App\Models\PickupPoint;
use App\Repositories\Base\BaseRepository;

class PickupPointRepository extends BaseRepository implements PickupPointRepositoryInterface
{
    public function __construct(PickupPoint $model)
    {
        parent::__construct($model, 'PickupPoint');
    }
}
