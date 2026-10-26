<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Payments Table (depends on quotes table)
        if (!Schema::hasTable('payments') && Schema::hasTable('quotes')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quote_id');
                $table->decimal('amount', 10, 2);
                $table->date('payment_date');
                $table->string('payment_method');
                $table->string('transaction_reference')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('quote_id')->references('id')->on('quotes')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users');
            });
        }

        // 2. Quotes Table Columns
        if (Schema::hasTable('quotes')) {
            Schema::table('quotes', function (Blueprint $table) {
                if (!Schema::hasColumn('quotes', 'shipment_id')) {
                    $table->unsignedBigInteger('shipment_id')->nullable()->after('created_by');
                    if (Schema::hasTable('shipments')) {
                        $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('set null');
                    }
                }
                if (!Schema::hasColumn('quotes', 'inventory_type')) {
                    $table->string('inventory_type')->nullable()->after('shipment_id');
                }
                if (!Schema::hasColumn('quotes', 'inventory_id')) {
                    $table->unsignedBigInteger('inventory_id')->nullable()->after('inventory_type');
                    $table->index(['inventory_type', 'inventory_id']);
                }
            });
        }

        // 3. Settings Table
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('type')->default('string');
                $table->string('group')->default('general');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dropping columns/tables if needed, but usually safe to leave or handle manually in dev
        Schema::dropIfExists('payments');
    }
};
