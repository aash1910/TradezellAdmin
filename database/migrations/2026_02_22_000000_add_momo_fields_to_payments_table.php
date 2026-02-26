<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMomoFieldsToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Track which gateway processed this payment
            $table->string('payment_gateway', 20)->default('stripe')->after('id');

            // MTN MoMo specific fields
            $table->string('momo_reference_id')->nullable()->unique()->after('stripe_payment_method_id');
            $table->string('momo_phone_number')->nullable()->after('momo_reference_id');
        });

        // Make stripe_payment_intent_id nullable so MoMo payments (which have no Stripe
        // intent) can coexist in the same table without violating the NOT NULL constraint.
        DB::statement('ALTER TABLE payments MODIFY COLUMN stripe_payment_intent_id VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Before reverting to NOT NULL, any null values must already be removed manually.
        DB::statement('ALTER TABLE payments MODIFY COLUMN stripe_payment_intent_id VARCHAR(255) NOT NULL');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['momo_reference_id']);
            $table->dropColumn(['payment_gateway', 'momo_reference_id', 'momo_phone_number']);
        });
    }
}
