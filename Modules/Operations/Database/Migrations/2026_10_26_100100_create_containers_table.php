<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->string('container_number')->index();
            $table->string('size_type')->nullable(); // 20GP, 40GP, 40HC (the sheet's "container series")
            $table->string('seal_number')->nullable();
            $table->foreignId('shipping_line_id')->nullable()->constrained('shipping_lines')->nullOnDelete();
            $table->string('booking_number')->nullable();
            $table->string('bl_number')->nullable();
            $table->string('vessel')->nullable();
            $table->string('voyage')->nullable();
            $table->string('port_of_loading')->nullable();
            $table->string('port_of_discharge')->nullable();
            $table->string('status')->default('planned');
            $table->unsignedTinyInteger('capacity')->default(4);
            $table->date('loaded_on')->nullable();
            $table->date('etd')->nullable();
            $table->date('sailed_on')->nullable();
            $table->date('eta')->nullable();
            $table->date('arrived_on')->nullable();
            $table->date('offloaded_on')->nullable();
            $table->date('released_on')->nullable();
            $table->decimal('freight_cost', 15, 2)->nullable();
            $table->string('freight_currency', 3)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('containers');
    }
};
