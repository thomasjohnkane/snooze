<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduledNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('scheduled-notifications.ssn_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('type');
            $table->json('data')->nullable();
            $table->datetime('send_at');
            $table->tinyInteger('sent')->default(0);
            $table->tinyInteger('rescheduled')->default(0);
            $table->tinyInteger('cancelled')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('scheduled-notifications.ssn_table'));
    }
}
