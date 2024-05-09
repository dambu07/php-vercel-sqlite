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
        Schema::table('prescriptions_medicines', function (Blueprint $table) {
            $table->integer('dose_interval')->after('time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('prescriptions_medicines', 'dose_interval')) {
            Schema::table('prescriptions_medicines', function (Blueprint $table) {
                $table->dropColumn('dose_interval');
            });
        }
    }
};
