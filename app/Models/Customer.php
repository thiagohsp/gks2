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
        'customer_balance'
    ];

    protected $casts = [
        'customer_balance' => 'float'
    ];

    public function invoices() {
        return $this->hasMany(Invoice::class, 'customer_id', 'id');
    }

}
