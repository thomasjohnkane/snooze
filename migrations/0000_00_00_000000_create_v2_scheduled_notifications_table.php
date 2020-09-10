<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2ScheduledNotificationsTable extends Migration
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
            $table->mediumText('target');

            $table->string('notification_type');
            $table->mediumText('notification');

            $table->datetime('send_at');
            $table->tinyInteger('tries')->default(0);

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
