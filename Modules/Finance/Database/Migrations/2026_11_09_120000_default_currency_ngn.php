<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Naira is the business's default currency: new invoices and charges are NGN
 * unless a different currency is given.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', fn (Blueprint $table) => $table->string('currency', 3)->default('NGN')->change());
        Schema::table('shipment_charges', fn (Blueprint $table) => $table->string('currency', 3)->default('NGN')->change());
    }

    public function down(): void
    {
        Schema::table('invoices', fn (Blueprint $table) => $table->string('currency', 3)->default('USD')->change());
        Schema::table('shipment_charges', fn (Blueprint $table) => $table->string('currency', 3)->default('USD')->change());
    }
};
