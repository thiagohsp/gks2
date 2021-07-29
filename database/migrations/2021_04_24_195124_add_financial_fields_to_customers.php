<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinancialFieldsToCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('valor_nota_nfe', 15,2)->nullable();
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
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
}
