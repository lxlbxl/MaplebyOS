<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Guard: older installs created this via SQL dumps before module migrations were loaded.
        if (Schema::hasColumn('quotes', 'shipment_id')) {
            return;
        }
        Schema::table('quotes', function (Blueprint $table) {
            $table->foreignId('shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
            $table->nullableMorphs('inventory');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropForeign(['shipment_id']);
            $table->dropColumn('shipment_id');
            $table->dropMorphs('inventory');
        });
    }
};
