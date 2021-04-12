<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use YourAppRocks\EloquentUuid\Traits\HasUuid;

class Bill extends Model
{
    use HasFactory, HasUuid;

    protected $uuidColumnName = 'id';
    protected $keyType = 'string';
    public $incrementing = false;


    protected $fillable = [

        'bill_number',
        'due_date',
        'payment_date',
        'value',
        'payment_value',
        'net_value',
        'link',
        'invoice_id',
        'account_id',
        'batch_id'
    ];

    protected $casts = [

        'due_date' => 'datetime',
        'payment_date' => 'datetime',
        'value' => 'float',
        'payment_value' => 'float',
        'net_value' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function invoice() {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    public function account() {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function batch() {
        return $this->belongsTo(Batch::class, 'batch_id', 'id');
    }

}
