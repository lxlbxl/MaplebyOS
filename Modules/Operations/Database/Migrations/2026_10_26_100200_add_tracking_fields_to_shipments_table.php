<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A shipment row is one vehicle. These columns cover every field of the
 * MAPLEBY TRACKING SYSTEM sheet plus purchase details.
 */
return new class extends Migration {
    private const STATUS_MAP = [
        'pending' => 'added',
        'draft' => 'added',
        'received' => 'at_yard',
        'scheduled' => 'at_yard',
        'shipped' => 'sailed',
        'arrived' => 'arrived',
        'delivered' => 'delivered',
    ];

    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('tracking_id')->nullable()->unique()->after('shipment_number');
            $table->foreignId('container_id')->nullable()->after('client_id')->constrained('containers')->nullOnDelete();
            $table->unsignedTinyInteger('container_slot')->nullable()->after('container_id');
            $table->date('date_added')->nullable();
            $table->date('loaded_on')->nullable();
            $table->date('offloaded_on')->nullable();
            $table->date('delivered_on')->nullable();
            $table->string('destination_city')->nullable();
            $table->string('vehicle_trim')->nullable();
            $table->boolean('has_keys')->nullable();
            $table->string('purchase_platform')->nullable();
            $table->string('lot_number')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->index('status');
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->change();
            $table->string('service_type')->nullable()->change();
            $table->string('title')->nullable()->change();
            $table->string('status')->default('added')->change();
        });

        foreach (self::STATUS_MAP as $old => $new) {
            DB::table('shipments')->where('status', $old)->update(['status' => $new]);
        }

        // Give existing rows a tracking ID in the MAP{YY}-{MM}-{seq} format.
        $counters = [];
        DB::table('shipments')->whereNull('tracking_id')->orderBy('created_at')->orderBy('id')
            ->get(['id', 'created_at', 'date_received'])
            ->each(function ($row) use (&$counters) {
                $date = Carbon::parse($row->date_received ?? $row->created_at ?? now());
                $prefix = 'MAP' . $date->format('y') . '-' . $date->format('m') . '-';
                $counters[$prefix] = ($counters[$prefix] ?? 0) + 1;
                do {
                    $id = $prefix . str_pad((string) $counters[$prefix], 3, '0', STR_PAD_LEFT);
                    $taken = DB::table('shipments')->where('tracking_id', $id)->exists();
                    if ($taken) {
                        $counters[$prefix]++;
                    }
                } while ($taken);

                DB::table('shipments')->where('id', $row->id)->update([
                    'tracking_id' => $id,
                    'date_added' => $date->toDateString(),
                ]);
            });
    }

    public function down(): void
    {
        foreach (array_flip(['pending' => 'added', 'received' => 'at_yard', 'shipped' => 'sailed', 'arrived' => 'arrived', 'delivered' => 'delivered']) as $new => $old) {
            DB::table('shipments')->where('status', $new)->update(['status' => $old]);
        }

        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['container_id']);
            $table->dropUnique(['tracking_id']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'tracking_id', 'container_id', 'container_slot', 'date_added', 'loaded_on', 'offloaded_on',
                'delivered_on', 'destination_city', 'vehicle_trim', 'has_keys', 'purchase_platform',
                'lot_number', 'purchase_date', 'purchase_price',
            ]);
        });
    }
};
