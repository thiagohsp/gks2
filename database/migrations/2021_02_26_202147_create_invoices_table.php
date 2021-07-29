<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->date('date');
            $table->bigInteger('number');
            $table->string('key', 44);
            $table->string('serie', 2);
            $table->string('cfop', 4);
            $table->decimal('value', 15,2);
            $table->decimal('balance', 15,2);
            $table->string('agent', 2)->nullable();
            $table->string('agent_2')->nullable();
            $table->string('operation')->nullable();
            $table->string('status');
            $table->string('last_letter', 2)->nullable();

            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
