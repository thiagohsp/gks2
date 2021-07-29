<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Collection;

interface InvoiceRepositoryInterface extends EloquentRepositoryInterface {

    public function findByKey(
        string $key,
        array $columns = ['*'],
        array $relations = []
    ): ?Collection;

}
