<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the name column as we'll split it into first_name and last_name
            $table->dropColumn('name');
            
            // Add new columns
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');
            $table->string('status')->default('active')->after('password');
            $table->string('image')->nullable()->after('status');
            $table->string('document')->nullable()->after('image');
            $table->text('address')->nullable()->after('document');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->date('date_of_birth')->nullable()->after('address');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->string('nationality')->nullable()->after('gender');
            $table->string('mobile')->nullable()->after('nationality');
            $table->string('otp')->nullable()->after('mobile');
            $table->boolean('is_verified')->default(false)->after('otp');
            $table->timestamp('otp_expires_at')->nullable()->after('is_verified');
            $table->json('settings')->nullable()->after('otp_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add back the name column
            $table->string('name')->after('id');
            
            // Drop the new columns
            $table->dropColumn([
                'first_name',
                'last_name',
                'status',
                'image',
                'document',
                'address',
                'date_of_birth',
                'gender',
                'nationality',
                'mobile',
                'otp',
                'is_verified',
                'otp_expires_at'
            ]);
        });
    }
} 