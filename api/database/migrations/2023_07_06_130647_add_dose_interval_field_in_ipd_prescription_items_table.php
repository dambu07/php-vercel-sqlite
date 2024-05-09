<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ipd_prescription_items', function (Blueprint $table) {
            $table->integer('dose_interval')->after('dosage');
            $table->string('day')->after('dose_interval')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ipd_prescription_items', function (Blueprint $table) {
            $table->dropColumn('dose_interval');
            $table->dropColumn('day');
        });
    }
};
