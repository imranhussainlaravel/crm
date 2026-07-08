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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('source');
            $table->string('status')->default('new');
            $table->string('product_interest');
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reassigned_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reassigned_at')->nullable();
            $table->string('lost_reason')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('follow_up_note')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
