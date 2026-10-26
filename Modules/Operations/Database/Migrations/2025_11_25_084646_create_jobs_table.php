<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->unique();
            $table->unsignedBigInteger('quote_id')->nullable(); // Constraint added later
            $table->foreignId('client_id')->constrained('clients');
            $table->string('service_type');
            $table->string('status')->default('pending');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->date('estimated_completion')->nullable();
            $table->date('actual_completion')->nullable();
            $table->decimal('total_value', 15, 2)->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('invoice_status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->string('priority')->default('medium');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
