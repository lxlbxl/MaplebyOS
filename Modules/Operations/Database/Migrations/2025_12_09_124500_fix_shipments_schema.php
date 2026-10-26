<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('jobs') && !Schema::hasTable('shipments')) {
            Schema::rename('jobs', 'shipments');
        }
        if (Schema::hasTable('shipments') && Schema::hasColumn('shipments', 'job_number')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->renameColumn('job_number', 'shipment_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('shipments') && Schema::hasColumn('shipments', 'shipment_number')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->renameColumn('shipment_number', 'job_number');
            });
        }
        if (Schema::hasTable('shipments')) {
            Schema::rename('shipments', 'jobs');
        }
    }
};
