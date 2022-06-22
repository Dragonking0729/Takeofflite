<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWcywColumnsToUserCostItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_cost_items', function (Blueprint $table) {
            $table->string('wcyw_number', 255)->nullable();
            $table->decimal('wcyw_price', 18, 2)->nullable();
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
            $table->dropColumn('wcyw_number');
            $table->dropColumn('wcyw_price');
        });
    }
}
