<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
    
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('reviewer_id'); // Who gave the review (sender or dropper)
            $table->unsignedBigInteger('reviewee_id'); // Who is being reviewed
    
            $table->tinyInteger('rating')->unsigned(); // 1 to 5
            $table->text('review_text')->nullable();
    
            $table->timestamps();
    
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewee_id')->references('id')->on('users')->onDelete('cascade');
        });
    }    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
