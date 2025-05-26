<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'delivered'])->default('active')->after('price');
        });

        // Update existing packages with orders that are completed
        DB::statement("
            UPDATE packages p 
            INNER JOIN orders o ON p.id = o.package_id 
            SET p.status = 'delivered' 
            WHERE o.status = 'completed'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}; 