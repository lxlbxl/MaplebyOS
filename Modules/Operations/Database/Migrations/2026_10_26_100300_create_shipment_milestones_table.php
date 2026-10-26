<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Status history for vehicles and containers. A container milestone is copied
 * to each vehicle in it, so every car has a complete timeline.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipment_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->nullable()->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('container_id')->nullable()->constrained('containers')->cascadeOnDelete();
            $table->string('status');
            $table->date('occurred_on');
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['shipment_id', 'occurred_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_milestones');
    }
};
