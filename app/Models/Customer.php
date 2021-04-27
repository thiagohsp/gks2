<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use YourAppRocks\EloquentUuid\Traits\HasUuid;

class Customer extends Model
{
    use HasFactory, HasUuid;

    protected $uuidColumnName = 'id';
    protected $keyType = 'string';
    public $incrementing = false;


    protected $fillable = [
        'document_number',
        'social_name',
        'adress_street',
        'adress_number',
        'adress_complement',
        'adress_district',
        'adress_zipcode',
        'adress_city',
        'adress_state',
        'adress_country',
        'email',
        'customer_balance',
        'is_active',

        'maino_customer_id',
        'valor_nota_nfe',
        'total_devolucoes',
        'valor_pedido_liquido',
        'total_faturado',
        'total_liquidado',
        'falta_faturar',
        'falta_liquidar',
    ];

    protected $casts = [
        'customer_balance' => 'float',
        'valor_nota_nfe'  => 'float',
        'total_devolucoes'  => 'float',
        'valor_pedido_liquido'  => 'float',
        'total_faturado'  => 'float',
        'total_liquidado'  => 'float',
        'falta_faturar'  => 'float',
        'falta_liquidar'  => 'float',
    ];

    public function invoices() {
        return $this->hasMany(Invoice::class, 'customer_id', 'id');
    }

}
