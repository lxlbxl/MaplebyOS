<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Who added each car, so a Puncher can fix their own entries until the car is loaded.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('shipments', 'created_by')) {
            return;
        }

        Schema::table('shipments', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->index();
        });

        // Back-fill from the "Vehicle added" milestone where we have one.
        if (Schema::hasTable('shipment_milestones')) {
            $rows = DB::table('shipment_milestones')
                ->whereNotNull('user_id')
                ->where('note', 'Vehicle added')
                ->get(['shipment_id', 'user_id']);
            foreach ($rows as $row) {
                DB::table('shipments')->where('id', $row->shipment_id)->whereNull('created_by')->update(['created_by' => $row->user_id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
