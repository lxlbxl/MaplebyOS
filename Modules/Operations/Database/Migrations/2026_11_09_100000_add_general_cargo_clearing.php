<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * General cargo clearing. A shipment row is now either a vehicle (the original
 * car export flow) or a clearing job: one Bill of Lading of a client's general
 * cargo arriving in Nigeria, held in one or more containers.
 *
 * Existing rows become cargo_type = 'vehicle', so car data is unchanged.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('cargo_type', 20)->default('vehicle')->index();

            // What is in the boxes.
            $table->string('cargo_description')->nullable();
            $table->string('hs_code', 20)->nullable();
            $table->unsignedInteger('package_count')->nullable();
            $table->string('package_type', 40)->nullable();
            $table->decimal('gross_weight_kg', 12, 2)->nullable();
            $table->decimal('volume_cbm', 10, 3)->nullable();
            $table->decimal('declared_value', 15, 2)->nullable();
            $table->string('declared_currency', 3)->nullable();
            $table->string('consignee')->nullable();

            // Clearing paperwork.
            $table->string('bl_number')->nullable()->index();
            $table->string('form_m_number')->nullable();
            $table->string('paar_number')->nullable();
            $table->string('customs_entry_number')->nullable(); // SGD / C-number
            $table->string('terminal')->nullable();
            $table->decimal('duty_assessed', 15, 2)->nullable();

            // Clearing milestones (loaded_on/offloaded_on/delivered_on already exist).
            $table->date('eta')->nullable();
            $table->date('arrived_on')->nullable();
            $table->date('duty_assessed_on')->nullable();
            $table->date('duty_paid_on')->nullable();
            $table->date('examined_on')->nullable();
            $table->date('released_on')->nullable();
            $table->date('empty_returned_on')->nullable();
        });

        Schema::table('containers', function (Blueprint $table) {
            // Set when the container carries a clearing job's general cargo.
            $table->foreignId('cargo_shipment_id')->nullable()->after('id')->constrained('shipments')->nullOnDelete();
            $table->date('empty_returned_on')->nullable()->after('released_on');
        });
    }

    public function down(): void
    {
        Schema::table('containers', function (Blueprint $table) {
            $table->dropForeign(['cargo_shipment_id']);
            $table->dropColumn(['cargo_shipment_id', 'empty_returned_on']);
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->dropIndex(['cargo_type']);
            $table->dropIndex(['bl_number']);
            $table->dropColumn([
                'cargo_type', 'cargo_description', 'hs_code', 'package_count', 'package_type', 'gross_weight_kg',
                'volume_cbm', 'declared_value', 'declared_currency', 'consignee', 'bl_number', 'form_m_number',
                'paar_number', 'customs_entry_number', 'terminal', 'duty_assessed', 'eta', 'arrived_on',
                'duty_assessed_on', 'duty_paid_on', 'examined_on', 'released_on', 'empty_returned_on',
            ]);
        });
    }
};
