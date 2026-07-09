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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('sales_rep_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stage')->default('quoted');
            $table->decimal('value', 12, 2)->nullable();
            $table->date('expected_close_date')->nullable();
            $table->unsignedTinyInteger('probability')->nullable();
            $table->string('lost_reason')->nullable();
            $table->flowforgePositionColumn();
            $table->timestamps();

            $table->unique(['stage', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
