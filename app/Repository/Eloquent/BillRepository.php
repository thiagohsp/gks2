<?php

namespace App\Repository\Eloquent;

use App\Models\Bill;
use App\Repository\BillRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class BillRepository extends BaseRepository implements BillRepositoryInterface
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
