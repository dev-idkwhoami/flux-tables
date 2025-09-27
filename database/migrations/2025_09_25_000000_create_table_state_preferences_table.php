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
        Schema::create('table_state_preferences', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('state_id'); // Composite ID: ModelClass|ID (e.g., 'App\Models\User|1', 'session|abc123')
            $table->string('table_id'); // Table identifier (e.g., 'users_table')
            $table->string('state_type'); // StateType enum value

            // Data storage
            $table->json('state_data'); // Flexible JSON storage for any state type

            // Metadata
            $table->timestamp('expires_at')->nullable(); // TTL support
            $table->json('metadata')->nullable(); // Additional metadata

            // Timestamps
            $table->timestamps();

            // Indexes for performance (with explicit names to avoid conflicts)
            $table->index(['state_id'], 'tsp_state_id_idx');
            $table->index(['table_id'], 'tsp_table_id_idx');
            $table->index(['state_type'], 'tsp_state_type_idx');
            $table->index(['state_id', 'table_id'], 'tsp_state_table_idx');
            $table->index(['state_id', 'table_id', 'state_type'], 'tsp_state_table_type_idx');
            $table->index(['table_id', 'state_type'], 'tsp_table_type_idx');
            $table->index(['expires_at'], 'tsp_expires_at_idx');

            // Unique constraint to prevent duplicates
            $table->unique(['state_id', 'table_id', 'state_type'], 'tsp_unique_state_pref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_state_preferences');
    }
};
