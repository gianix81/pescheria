<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('response_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('responses')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 40);                 // BOZZA_SALVATA, INVIO, MODIFICA, RIFIUTO, RIAPERTURA, ...
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->unsignedInteger('from_packages')->nullable();
            $table->unsignedInteger('to_packages');
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['response_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('response_revisions');
    }
};
