<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('data_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->string('confirmation_code')->unique();
            $table->string('user_id')->nullable();
            $table->string('facebook_user_id')->nullable();
            $table->string('status')->default('pending');
            $table->text('request_data')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            // Add index for faster lookups
            $table->index(['confirmation_code', 'status']);
            $table->index('facebook_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_deletion_requests');
    }
}; 