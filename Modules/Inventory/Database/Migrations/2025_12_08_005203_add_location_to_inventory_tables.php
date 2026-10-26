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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('location')->nullable()->after('status');
        });
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('location')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('location');
        });
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};
