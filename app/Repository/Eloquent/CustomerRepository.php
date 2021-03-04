<?php

namespace App\Repository\Eloquent;

use App\Models\Customer;
use App\Repository\CustomerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
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
    public function __construct(Customer $model)
    {
        $this->model = $model;
    }

    public function findByDocument(
        string $document_number,
        array $columns = ['*'],
        array $relations = []
    ): ?Collection {
        return $this->model->with($relations)->where('document_number', '=', $document_number)->get($columns);
    }

}
