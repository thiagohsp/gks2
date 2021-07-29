<?php

use Illuminate\Database\Events\SchemaDumped;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('social_name', 60);
            $table->string('document_number', 14);
            $table->string('adress_street', 60);
            $table->string('adress_number', 30  );
            $table->string('adress_complement', 60);
            $table->string('adress_district', 60);
            $table->string('adress_city', 60);
            $table->string('adress_state', 2);
            $table->string('adress_zipcode', 8);
            $table->string('adress_country', 2);
            $table->string('email', 100);
            $table->string('maino_api_key');

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
        Schema::dropIfExists('companies');
    }
}
