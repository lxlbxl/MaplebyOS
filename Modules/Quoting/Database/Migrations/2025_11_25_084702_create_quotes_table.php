<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Guard: older installs created this via SQL dumps before module migrations were loaded.
        if (Schema::hasTable('quotes')) {
            return;
        }
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->unique(); // Q-YYYYMMDD-XXX
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('lead_id')->nullable()->constrained('leads');
            $table->string('status')->default('draft'); // draft, sent, approved, rejected, expired, converted
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable(); // Internal notes
            $table->text('terms_conditions')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
