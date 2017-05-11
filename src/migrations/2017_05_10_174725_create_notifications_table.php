<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('message');
            $table->string('notification_type', 20)->nullable();
            $table->longText('meta')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('user_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->unsigned();
            $table->integer('notification_id')->unsigned();
            $table->tinyInteger('is_read');
            $table->timestamps();

            $table->foreign('notification_id')
            ->references('id')->on('notifications')
            ->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('notifications');
        Schema::drop('user_notifications');
    }
}
