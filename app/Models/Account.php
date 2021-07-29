<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use YourAppRocks\EloquentUuid\Traits\HasUuid;

class Account extends Model
{
    use HasFactory, HasUuid;

    protected $uuidColumnName = 'id';
    protected $keyType = 'string';
    public $incrementing = false;


    protected $fillable = [

        'codigo_conta_corrente_maino',
        'bank_number',
        'bank_name',
        'label',
        'agency',
        'account',
        'allow_pjbank_bills',
        'active',

    ];


}
