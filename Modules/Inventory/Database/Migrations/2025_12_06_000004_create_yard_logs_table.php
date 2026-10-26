<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('yard_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('inventory'); // vehicle_id or equipment_id
            $table->string('status'); // e.g., 'arrived', 'inspected', 'ready_to_ship'
            $table->foreignId('logged_by')->constrained('users');
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yard_logs');
    }
};
