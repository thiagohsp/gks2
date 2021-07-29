<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinancialFieldsToInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
            $table->decimal('total_devolucoes', 15,2)->nullable();
            $table->decimal('valor_pedido_liquido', 15,2)->nullable();
            $table->decimal('total_faturado', 15,2)->nullable();
            $table->decimal('total_liquidado', 15,2)->nullable();
            $table->decimal('falta_faturar', 15,2)->nullable();
            $table->decimal('falta_liquidar', 15,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice', function (Blueprint $table) {
            //
        });
    }
}
