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
        Schema::table('patient_credit_cards', function (Blueprint $table) {
            // Add new columns
            $table->string('expiry_month')->after('cvc_encrypted');
            $table->string('expiry_year')->after('expiry_month');
            
            // Drop unused columns
            $table->dropColumn([
                'exp_date',
                'street_address',
                'city', 
                'state',
                'zip_code',
                'country'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_credit_cards', function (Blueprint $table) {
            // Add back the old columns
            $table->string('exp_date')->after('cvc_encrypted');
            $table->string('street_address')->after('name_on_card');
            $table->string('city')->after('street_address');
            $table->string('state')->after('city');
            $table->string('zip_code')->after('state');
            $table->string('country')->after('zip_code');
            
            // Drop the new columns
            $table->dropColumn(['expiry_month', 'expiry_year']);
        });
    }
};
