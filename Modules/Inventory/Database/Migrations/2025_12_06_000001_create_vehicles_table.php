<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('make');
            $table->string('model');
            $table->integer('year');
            $table->string('vin')->unique();
            $table->string('color')->nullable();
            $table->string('condition')->default('used'); // used, new, salvage
            $table->string('status')->default('in_stock'); // in_stock, sold, shipped
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('sale_date')->nullable();
            $table->foreignId('buyer_id')->nullable()->constrained('clients');
            $table->foreignId('supplier_id')->nullable()->constrained('clients');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
