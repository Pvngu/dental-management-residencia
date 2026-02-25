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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->string('action'); // Type of action (e.g., 'created', 'updated', 'login')
            $table->string('entity')->nullable(); // Optional: Type of related entity (e.g., 'User', 'Post')
            $table->timestamp('datetime')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->text('description')->nullable();           
            $table->jsonb('user')->nullable(); // User who performed the action (assuming a 'users' table)
            $table->bigInteger('patient_id')->unsigned()->nullable();
            $table->jsonb('json_log')->nullable(); // Additional details or context
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            
            // Setting table options
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->index(['patient_id', 'created_at']);
            $table->index(['patient_id', 'company_id']);
        });
        
        
        // Particionar por RANGO de id
        DB::statement('
            ALTER TABLE activity_logs 
            PARTITION BY RANGE (id) (
                PARTITION p0 VALUES LESS THAN (1000000),
                PARTITION p1 VALUES LESS THAN (2000000),
                PARTITION p2 VALUES LESS THAN (3000000),
                PARTITION p3 VALUES LESS THAN (4000000),
                PARTITION p4 VALUES LESS THAN (5000000),
                PARTITION pmax VALUES LESS THAN MAXVALUE
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
