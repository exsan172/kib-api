<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->string('role_type', 191);
            $table->string('role_name', 191);
            $table->softDeletes();
            $table->timestamps();
        });

        DB::table('roles')->insert([
            [
                'id' => 1,
                'uuid' => Uuid::uuid4(),
                'role_type' => 'superadmin',
                'role_name' => 'Superadmin',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 2,
                'uuid' => Uuid::uuid4(),
                'role_type' => 'admin',
                'role_name' => 'Admin',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 3,
                'uuid' => Uuid::uuid4(),
                'role_type' => 'member',
                'role_name' => 'Member',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
