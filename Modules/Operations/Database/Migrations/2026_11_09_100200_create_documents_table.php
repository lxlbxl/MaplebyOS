<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Scanned or uploaded papers (Bill of Lading, title, dock receipt...) attached
 * to a car or a container, tagged with a type and the stage they belong to.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable');
            $table->string('type', 40);
            $table->string('stage', 30)->nullable();
            $table->boolean('sensitive')->default(false);
            $table->string('original_name');
            $table->string('path');
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
