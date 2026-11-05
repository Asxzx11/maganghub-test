<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->string('category');
            $table->string('location')->nullable();
            $table->string('current_condition')->default('baik');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('technicians', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('department')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('loan_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained()->restrictOnDelete();
            $table->foreignId('technician_id')->constrained()->restrictOnDelete();
            $table->timestamp('borrowed_at');
            $table->date('due_at');
            $table->timestamp('returned_at')->nullable();
            $table->string('return_condition')->nullable();
            $table->text('return_notes')->nullable();
            $table->timestamps();
            $table->index(['tool_id', 'returned_at']);
            $table->index(['returned_at', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_records');
        Schema::dropIfExists('technicians');
        Schema::dropIfExists('tools');
    }
};
