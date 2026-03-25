<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListingSwipesTable extends Migration
{
    public function up()
    {
        Schema::create('listing_swipes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');     // who swiped
            $table->unsignedBigInteger('listing_id');  // which listing was swiped
            $table->unsignedBigInteger('owner_id');    // owner of the listing (denormalized for quick lookup)
            $table->enum('direction', ['yes', 'no'])->default('no');
            $table->timestamps();

            $table->unique(['user_id', 'listing_id']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['owner_id', 'direction']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('listing_swipes');
    }
}
