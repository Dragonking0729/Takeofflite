<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBlsColumnsToUserCostItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_cost_items', function (Blueprint $table) {
            $table->string('bls_number', 255)->nullable();
            $table->decimal('bls_price', 18, 2)->nullable();
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
            $table->dropColumn('bls_number');
            $table->dropColumn('bls_price');
        });
    }
}
