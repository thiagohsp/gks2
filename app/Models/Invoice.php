<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use YourAppRocks\EloquentUuid\Traits\HasUuid;

class Invoice extends Model
{
    use HasFactory, HasUuid;

    protected $uuidColumnName = 'id';
    protected $keyType = 'string';
    public $incrementing = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key'           ,
        'number'        ,
        'date'          ,
        'serie'         ,
        'cfop'          ,
        'value'         ,
        'status'        ,
        'balance'       ,
        'last_letter'   ,
        'customer_id'   ,
        'agent'         ,
        'agent_2'       ,
        'operation'
    ];

    protected $casts = [
        'date' => 'datetime',
        'value' => 'float',
        'balance' => 'float',
        'cfop' => 'integer'
    ];

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
