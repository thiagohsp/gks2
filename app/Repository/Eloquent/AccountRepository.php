<?php

namespace App\Repository\Eloquent;

use App\Models\Account;
use App\Repository\AccountRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AccountRepository extends BaseRepository implements AccountRepositoryInterface
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
    public function __construct(Account $model)
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
