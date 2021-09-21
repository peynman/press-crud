<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->bigIncrements('id');
            $table->integer('author_id', false, true);
            $table->string('name');
            $table->string('title');
            $table->integer('priority', false, true)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['deleted_at', 'created_at', 'updated_at', 'name']);

            $table->unique(['deleted_at', 'name']);

            $table->foreign('author_id')->references('id')->on('users');
        });
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
