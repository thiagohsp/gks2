<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('document_number', 14);
            $table->string('social_name', 100);
            $table->string('adress_street', 60);
            $table->string('adress_number', 30);
            $table->string('adress_complement', 60)->nullable();
            $table->string('adress_district', 60);
            $table->string('adress_zipcode', 8);
            $table->string('adress_city', 60);
            $table->string('adress_state', 2);
            $table->string('adress_country', 2);
            $table->string('email', 100);
            $table->decimal('customer_balance', 15, 2);

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
        Schema::dropIfExists('customers');
    }
}
