<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchesTable extends Migration
{
    public function up()
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_one_id');
            $table->unsignedBigInteger('user_two_id');
            $table->enum('status', ['active', 'unmatched'])->default('active');
            $table->timestamp('unmatched_at')->nullable();
            $table->unsignedBigInteger('conversation_id')->nullable(); // link to messages thread
            $table->timestamps();

            $table->unique(['user_one_id', 'user_two_id']);

            $table->foreign('user_one_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_two_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['user_one_id', 'status']);
            $table->index(['user_two_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('matches');
    }
}
