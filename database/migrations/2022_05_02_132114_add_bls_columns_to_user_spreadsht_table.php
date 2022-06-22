<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBlsColumnsToUserSpreadshtTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_spreadsht', function (Blueprint $table) {
            $table->string('ss_bls_number', 255)->nullable()->comment('from cost item table');
            $table->decimal('ss_bls_price', 18, 2)->nullable()->comment('from cost item table');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_spreadsht', function (Blueprint $table) {
            $table->dropColumn('ss_bls_number');
            $table->dropColumn('ss_bls_price');
        });
    }
}
