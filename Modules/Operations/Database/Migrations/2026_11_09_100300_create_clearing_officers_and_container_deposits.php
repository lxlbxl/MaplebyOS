<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clearing officers and container deposits.
 *
 * Clearing officers: each clearing (a car container, or a general cargo
 * clearing job with all its containers) has one officer at a time. Every
 * assignment is kept, and each piece of work is credited to the officer who
 * held the clearing when it was done. Credit only moves when someone with
 * oversight overrides it, and both reassignments and overrides go to the
 * activity log.
 *
 * Container deposits: what was paid to the shipping line to release a
 * container, kept separate from the refund that comes back after the empty
 * container is returned. Refund follow-ups are logged one by one.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('clearing_officers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->string('licence_number', 60)->nullable(); // customs licence / agent code
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // if they also have a staff login
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('clearing_assignments', function (Blueprint $table) {
            $table->id();
            $table->morphs('clearable'); // a car container, or a general cargo clearing job
            $table->foreignId('clearing_officer_id')->constrained('clearing_officers');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 500)->nullable();
            $table->timestamps();
            $table->index(['clearable_type', 'clearable_id', 'ended_at']);
        });

        Schema::create('clearing_work', function (Blueprint $table) {
            $table->id();
            $table->morphs('clearable');
            $table->foreignId('clearing_officer_id')->nullable()->constrained('clearing_officers');
            $table->foreignId('original_officer_id')->nullable()->constrained('clearing_officers');
            $table->foreignId('assignment_id')->nullable()->constrained('clearing_assignments')->nullOnDelete();
            $table->string('action', 40); // status, document, deposit, refund, paperwork, note
            $table->string('description', 500);
            $table->nullableMorphs('subject');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->foreignId('overridden_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('overridden_at')->nullable();
            $table->string('override_reason', 500)->nullable();
            $table->timestamps();
            $table->index(['clearing_officer_id', 'occurred_at']);
        });

        Schema::create('container_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->constrained('containers')->cascadeOnDelete();
            $table->foreignId('shipping_line_id')->nullable()->constrained('shipping_lines')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('NGN');
            $table->date('paid_on')->nullable();
            $table->string('paid_by', 20)->default('mapleby'); // mapleby | client
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('deposit_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_deposit_id')->unique()->constrained('container_deposits')->cascadeOnDelete();
            $table->string('status', 30)->default('awaiting_empty')->index();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete(); // who follows it up
            $table->date('claim_submitted_on')->nullable();
            $table->string('claim_reference')->nullable();
            $table->decimal('deductions', 15, 2)->default(0); // demurrage, damage, cleaning...
            $table->string('deduction_reason', 500)->nullable();
            $table->decimal('amount_received', 15, 2)->default(0);
            $table->date('received_on')->nullable();
            $table->string('received_reference')->nullable();
            $table->date('next_follow_up_on')->nullable()->index();
            $table->date('last_followed_up_on')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('deposit_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deposit_refund_id')->constrained('deposit_refunds')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('contacted_on');
            $table->string('channel', 20)->nullable(); // call, email, visit, whatsapp
            $table->string('contact_person')->nullable();
            $table->text('outcome');
            $table->date('next_follow_up_on')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_follow_ups');
        Schema::dropIfExists('deposit_refunds');
        Schema::dropIfExists('container_deposits');
        Schema::dropIfExists('clearing_work');
        Schema::dropIfExists('clearing_assignments');
        Schema::dropIfExists('clearing_officers');
    }
};
