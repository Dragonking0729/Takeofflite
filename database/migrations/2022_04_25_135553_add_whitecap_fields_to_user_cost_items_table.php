<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhitecapFieldsToUserCostItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_cost_items', function (Blueprint $table) {
            $table->string('whitecap_sku', 255)->nullable();
            $table->decimal('whitecap_price', 18, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_cost_items', function (Blueprint $table) {
            $table->dropColumn('whitecap_sku');
            $table->dropColumn('whitecap_price');
        });
    }
}