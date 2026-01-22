<?php

namespace App\Repositories\RouteVehicle;

use App\Models\RouteVehicle;
use App\Repositories\Base\BaseRepository;

class RouteVehicleRepository extends BaseRepository implements RouteVehicleRepositoryInterface
{
    public function __construct(RouteVehicle $model)
    {
        parent::__construct($model, 'RouteVehicle');
    }
}
