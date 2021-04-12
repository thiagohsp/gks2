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
        'operation'     ,
        'total_devolucoes',
        'valor_pedido_liquido',
        'total_faturado',
        'total_liquidado',
        'falta_faturar',
        'falta_liquidar',
    ];

    protected $casts = [
        'date' => 'datetime',
        'value' => 'float',
        'balance' => 'float',
        'cfop' => 'integer',
        'total_devolucoes'  => 'float',
        'valor_pedido_liquido'  => 'float',
        'total_faturado'  => 'float',
        'total_liquidado'  => 'float',
        'falta_faturar'  => 'float',
        'falta_liquidar'  => 'float',
    ];

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function bills() {
        return $this->hasMany(Bill::class, 'invoice_id', 'id');
    }

    public function getPreviousLetter($actual)
    {
        $length = strlen($actual);
        $letter = 'A';

        if ($actual == $letter)
            return 'A';

        while ($letter != strtoupper($actual)) {
            $array_letters[] = $letter;
            $letter = $this->getNextLetter($letter);
        }

        $array_letters[] = strtoupper($actual);

        return  $array_letters[array_search(strtoupper($actual), $array_letters) - 1];
    }

    public function getNextLetter($actual = null)
    {

        $letra = ($this->last_letter == null || $this->last_letter == '') ? 'A' : $this->last_letter;

        if (($letra == 'A' && ($this->bills()->get()->count() == 0)))
            return 'A';

        if ($actual != null)
            $letra = $actual;

        $sequencia = (strlen($letra) > 1 ? $letra[1] : 0);

        if ($letra[0] == 'Z') {
            $sequencia++;
            $letra = 'A';
        } else {
            $letra = $letra[0];
            $letra++;
        }

        return $letra . ($sequencia != '0' ? $sequencia : null);
    }
}
