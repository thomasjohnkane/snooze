<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->string('target_id')->nullable();
            $table->string('target_type');
            $table->text('target');

            $table->string('notification_type');
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
