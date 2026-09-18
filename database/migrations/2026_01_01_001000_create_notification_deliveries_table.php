<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->enum('channel', ['IN_APP', 'EMAIL', 'WHATSAPP']);
            $table->enum('status', ['PENDING', 'SENT', 'FAILED', 'SKIPPED'])->default('PENDING');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            // Chiave di idempotenza: impedisce notifiche duplicate se un job viene rieseguito.
            $table->string('dedupe_key', 191)->unique();
            $table->timestamps();

            $table->index(['status', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};
