<?php

namespace App\Repository\Eloquent;

use App\Models\Invoice;
use App\Repository\InvoiceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
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
    public function __construct(Invoice $model)
    {
        $this->model = $model;
    }

    public function findByKey(
        string $key,
        array $columns = ['*'],
        array $relations = []
    ): ?Collection {
        return $this->model->with($relations)->where('key', '=', $key)->get($columns);
    }

}
