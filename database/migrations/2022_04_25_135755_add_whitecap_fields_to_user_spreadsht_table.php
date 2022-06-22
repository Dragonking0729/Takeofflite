<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhitecapFieldsToUserSpreadshtTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_spreadsht', function (Blueprint $table) {
            $table->string('ss_whitecap_sku', 255)->nullable()->comment('from cost item table');
            $table->decimal('ss_whitecap_price', 18, 2)->nullable()->comment('from cost item table');
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
            $table->dropColumn('ss_whitecap_sku');
            $table->dropColumn('ss_whitecap_price');
        });
    }
}