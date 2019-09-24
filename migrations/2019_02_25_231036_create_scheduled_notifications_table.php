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

            $table->datetime('sent_at')->nullable();
            $table->datetime('rescheduled_at')->nullable();
            $table->datetime('cancelled_at')->nullable();

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
