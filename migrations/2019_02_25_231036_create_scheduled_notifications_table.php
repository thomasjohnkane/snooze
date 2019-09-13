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
        Schema::create(config('snooze.table'), function (Blueprint $table) {
            $table->increments('id');

            $table->string('type');
            $table->text('target');
            $table->text('notification');

            $table->datetime('send_at');

            $table->boolean('sent')->default(0);
            $table->boolean('rescheduled')->default(0);
            $table->boolean('cancelled')->default(0);

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
        Schema::dropIfExists(config('snooze.table'));
    }
}
