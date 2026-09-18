<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunity_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained('opportunities')->cascadeOnDelete();
            $table->enum('type', ['IMAGE', 'VIDEO']);
            $table->string('disk', 40);
            $table->string('path', 255);
            $table->string('poster_path', 255)->nullable();
            $table->string('original_name', 190)->nullable();
            $table->string('mime', 100);
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('sort_order')->default(0);
            // Punto di estensione per la scansione antivirus asincrona (assunzione A12).
            $table->enum('scan_status', ['PENDING', 'CLEAN', 'INFECTED', 'SKIPPED'])->default('SKIPPED');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['opportunity_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_media');
    }
};
