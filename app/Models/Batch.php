<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use YourAppRocks\EloquentUuid\Traits\HasUuid;

class Batch extends Model
{
    use HasFactory, HasUuid;

    protected $uuidColumnName = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'batch';


    protected $fillable = [

        'code',
        'total_value',
        'max_bill_value',
        'email',
        'status',

    ];

    protected $casts = [

        'total_value' => 'float',
        'max_bill_value' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function bills() {
        return $this->hasMany(Bill::class, 'batch_id', 'id');
    }

}
