<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Module;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Module::create([
            'name'      => 'Operation Categories',
            'is_active' => 1,
            'route'     => 'operation.category.index',
        ]);
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Module::where('name', 'Operation Categories')->delete();
    }
};
