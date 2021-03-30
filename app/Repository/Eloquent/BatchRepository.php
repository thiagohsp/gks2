<?php

namespace App\Repository\Eloquent;

use App\Models\Bill;
use App\Repository\BatchRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class BatchRepository extends BaseRepository implements BatchRepositoryInterface
{
    /**
     * @var Model
     */
    protected Model $model;

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Bill $model)
    {
        $this->model = $model;
    }

}
