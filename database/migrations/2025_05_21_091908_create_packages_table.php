<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
    
            // Sender Info (Foreign Key if linked to users)
            $table->unsignedBigInteger('sender_id')->nullable(); // if you have a users table
            $table->string('pickup_name');
            $table->string('pickup_mobile');
            $table->string('pickup_address');
            $table->text('pickup_details')->nullable();
            $table->float('weight');
            $table->decimal('price', 10, 2);
            $table->date('pickup_date');
            $table->time('pickup_time');
    
            // Drop Info
            $table->string('drop_name');
            $table->string('drop_mobile');
            $table->string('drop_address');
            $table->text('drop_details')->nullable();
    
            // Coordinates (optional but recommended for distance search)
            $table->decimal('pickup_lat', 10, 7)->nullable();
            $table->decimal('pickup_lng', 10, 7)->nullable();
            $table->decimal('drop_lat', 10, 7)->nullable();
            $table->decimal('drop_lng', 10, 7)->nullable();
    
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
        Schema::dropIfExists('packages');
    }
}
