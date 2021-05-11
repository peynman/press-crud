<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('author_id', false, true);
            $table->string('name')->unique();
            $table->json('data')->nullable();
            $table->integer('flags')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'deleted_at',
                'created_at',
                'updated_at',
                'author_id',
                'name',
                'flags',
            ], 'groups_full_index');

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
        Schema::dropIfExists('groups');
    }
}
