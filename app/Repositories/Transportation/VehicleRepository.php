<?php

namespace App\Repositories\Transportation;

use App\Models\Vehicle;
use App\Repositories\Base\BaseRepository;

class VehicleRepository extends BaseRepository implements VehicleRepositoryInterface
{
    public function __construct(Vehicle $model)
    {
        parent::__construct($model, 'vehicle');
    }

    // Add vehicle-specific methods if needed
}
