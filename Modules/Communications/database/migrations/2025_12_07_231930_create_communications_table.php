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
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->morphs('communicable');
            $table->string('type'); // email, call, meeting
            $table->string('direction'); // inbound, outbound
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->foreignId('contacted_by')->nullable()->constrained('users');
            $table->timestamp('contacted_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};
