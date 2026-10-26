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
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('vin')->nullable()->index()->after('shipment_number');
            $table->integer('vehicle_year')->nullable()->after('vin');
            $table->string('vehicle_make')->nullable()->after('vehicle_year');
            $table->string('vehicle_model')->nullable()->after('vehicle_make');
            $table->string('vehicle_color')->nullable()->after('vehicle_model');
            $table->boolean('tow_required')->default(false)->after('vehicle_color');
            $table->string('driver_name')->nullable()->after('tow_required');
            $table->boolean('ownership_documents')->default(false)->after('driver_name'); // For "OWNERSHIP" field
            $table->date('date_received')->nullable()->after('created_at'); // Legacy "Column 1"
            $table->text('location_notes')->nullable()->after('destination'); // Legacy "LOCATION"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'vin',
                'vehicle_year',
                'vehicle_make',
                'vehicle_model',
                'vehicle_color',
                'tow_required',
                'driver_name',
                'ownership_documents',
                'date_received',
                'location_notes',
            ]);
        });
    }
};
