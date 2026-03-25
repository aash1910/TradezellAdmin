<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListingsTable extends Migration
{
    public function up()
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['trade', 'sell', 'both'])->default('trade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('condition')->nullable(); // new, like_new, good, fair, poor
            $table->string('category')->nullable();
            $table->decimal('price', 10, 2)->nullable(); // only for type=sell or type=both
            $table->string('currency', 10)->default('USD');
            $table->json('images')->nullable(); // array of image paths / base64
            $table->enum('status', ['active', 'paused', 'sold', 'traded'])->default('active');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'status']);
            $table->index(['lat', 'lng']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('listings');
    }
}
