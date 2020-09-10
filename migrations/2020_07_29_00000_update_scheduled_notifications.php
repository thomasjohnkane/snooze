<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateScheduledNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(config('snooze.table'), function (Blueprint $table) {
            $table->mediumText('target')->change();
            $table->mediumText('notification')->change();
            $table->tinyInteger('tries')->default(0)->after('send_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('snooze.table'), function (Blueprint $table) {
            $table->text('target')->change();
            $table->text('notification')->change();
            $table->dropColumn('tries');
        });
    }
}
