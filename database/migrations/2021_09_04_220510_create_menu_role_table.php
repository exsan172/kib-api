<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMenuRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_role', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('menu_id')->unsigned();
            $table->foreignId('role_id');
            // $table->timestamps();

            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        DB::table('menu_role')->insert([
            [
                'menu_id' => 1,
                'role_id' => 1,
            ],
            [
                'menu_id' => 2,
                'role_id' => 1,
            ],
            [
                'menu_id' => 3,
                'role_id' => 1,
            ],
            [
                'menu_id' => 4,
                'role_id' => 1,
            ],
            [
                'menu_id' => 5,
                'role_id' => 1,
            ],
            [
                'menu_id' => 6,
                'role_id' => 1,
            ],
            [
                'menu_id' => 1,
                'role_id' => 2,
            ],
            [
                'menu_id' => 2,
                'role_id' => 2,
            ],
            [
                'menu_id' => 3,
                'role_id' => 2,
            ],
            [
                'menu_id' => 1,
                'role_id' => 3,
            ],
            [
                'menu_id' => 7,
                'role_id' => 3,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_role');
    }
}
