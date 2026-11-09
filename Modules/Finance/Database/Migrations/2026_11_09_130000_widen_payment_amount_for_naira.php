<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payments are in naira, so a single payment can pass ₦100M. Match the
 * 15,2 precision every other money column already uses.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', fn (Blueprint $table) => $table->decimal('amount', 15, 2)->change());
    }

    public function down(): void
    {
        Schema::table('payments', fn (Blueprint $table) => $table->decimal('amount', 10, 2)->change());
    }
};
