<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('bill_number'); // '00007700-C'
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->decimal('value', 15, 2);
            $table->decimal('payment_value', 15, 2)->nullable();
            $table->decimal('net_value', 15, 2)->nullable();
            $table->string('link')->nullable();

            $table->uuid('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('invoices');

            $table->uuid('account_id');
            $table->foreign('account_id')->references('id')->on('accounts');

            $table->uuid('batch_id');
            $table->foreign('batch_id')->references('id')->on('batch');

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
        Schema::dropIfExists('bills');
    }
}
