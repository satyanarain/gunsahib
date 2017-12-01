<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('task_complete_allowed');
            $table->integer('task_assign_allowed');

            $table->integer('lead_complete_allowed');
            $table->integer('lead_assign_allowed');
            $table->integer('time_change_allowed');
            $table->integer('comment_allowed');
            $table->string('country');
            $table->string('company');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('settings');
    }

}