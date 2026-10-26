<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // Excavator, Forklift, etc.
            $table->string('make');
            $table->string('model');
            $table->integer('year');
            $table->string('serial_number')->unique();
            $table->string('condition')->default('used');
            $table->string('status')->default('in_stock');
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->foreignId('buyer_id')->nullable()->constrained('clients');
            $table->foreignId('supplier_id')->nullable()->constrained('clients');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
