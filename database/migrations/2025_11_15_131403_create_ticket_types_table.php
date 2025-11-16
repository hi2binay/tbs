<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('currency', 3)->default('INR');
            $table->unsignedInteger('price_cents');
            $table->unsignedInteger('total_quantity');
            $table->unsignedInteger('sold_count')->default(0);
            $table->unsignedInteger('reserved_count')->default(0);
            $table->unsignedInteger('per_user_limit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['event_id', 'name']);
            $table->index('event_id');
            $table->index('is_active');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE ticket_types ADD CONSTRAINT check_inventory CHECK (sold_count >= 0 AND reserved_count >= 0 AND sold_count + reserved_count <= total_quantity)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
