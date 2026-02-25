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
        // Step 1: Add user_id column if it doesn't exist
        if (!Schema::hasColumn('attendances', 'user_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->bigInteger('user_id')->unsigned()->nullable()->after('company_id');
            });
        }
        
        // Step 2: Fix existing records with invalid user_ids
        // Get the first valid user ID for each company
        $companies = DB::table('companies')->get();
        foreach ($companies as $company) {
            // Try to get a user for this company
            $user = DB::table('users')->where('company_id', $company->id)->first();
            
            if ($user) {
                // Update attendances for this company with NULL or invalid user_ids
                DB::table('attendances')
                    ->where('company_id', $company->id)
                    ->where(function($query) use ($user) {
                        $query->whereNull('user_id')
                              ->orWhereNotIn('user_id', DB::table('users')->pluck('id'));
                    })
                    ->update(['user_id' => $user->id]);
            }
        }
        
        // Handle any remaining records without a company_id
        $defaultUser = DB::table('users')->orderBy('id')->first();
        if ($defaultUser) {
            DB::table('attendances')
                ->where(function($query) {
                    $query->whereNull('user_id')
                          ->orWhereNotIn('user_id', DB::table('users')->pluck('id'));
                })
                ->update(['user_id' => $defaultUser->id]);
        }
        
        // Step 3: Make column non-nullable and add foreign key if not exists
        Schema::table('attendances', function (Blueprint $table) {
            // Check if foreign key doesn't already exist
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'attendances' 
                AND COLUMN_NAME = 'user_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            if (empty($foreignKeys)) {
                $table->bigInteger('user_id')->unsigned()->nullable(false)->change();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop foreign key and column if they exist
            if (Schema::hasColumn('attendances', 'user_id')) {
                // Check if foreign key exists before dropping
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Exception $e) {
                    // Foreign key doesn't exist, continue
                }
                $table->dropColumn('user_id');
            }
        });
    }
};
