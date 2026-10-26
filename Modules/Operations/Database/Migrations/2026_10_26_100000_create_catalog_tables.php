<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Canonical lists that drive smart suggestions (makes, models, shipping lines,
 * purchase platforms, ports). Each entry carries aliases so common misspellings
 * resolve to the canonical name.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicle_makes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('aliases')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_make_id')->constrained('vehicle_makes')->cascadeOnDelete();
            $table->string('name');
            $table->json('aliases')->nullable();
            $table->timestamps();
            $table->unique(['vehicle_make_id', 'name']);
        });

        Schema::create('shipping_lines', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('scac', 8)->nullable();
            $table->json('aliases')->nullable();
            $table->json('container_prefixes')->nullable();
            $table->string('tracking_url')->nullable(); // use {number} as placeholder
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('purchase_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('aliases')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });

        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('country')->nullable();
            $table->string('kind')->default('destination'); // origin, destination
            $table->json('aliases')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ports');
        Schema::dropIfExists('purchase_platforms');
        Schema::dropIfExists('shipping_lines');
        Schema::dropIfExists('vehicle_models');
        Schema::dropIfExists('vehicle_makes');
    }
};
