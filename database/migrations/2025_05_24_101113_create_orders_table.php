<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id')->unique(); // 1 package, 1 order max
            $table->unsignedBigInteger('dropper_id'); // dropper = user who takes the delivery
            $table->enum('status', ['ongoing', 'active', 'canceled', 'completed'])->default('ongoing');
    
            $table->timestamps();
    
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
            $table->foreign('dropper_id')->references('id')->on('users')->onDelete('cascade');
        });
    }    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
