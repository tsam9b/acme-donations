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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('goal_amount', 12, 2)->nullable(); // Target amount to raise
            $table->decimal('current_amount', 12, 2)->default(0); // Current raised amount
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'completed', 'cancelled'])->default('active');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Campaign creator
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
